"""
TTD Processing Service (OpenCV Python)
======================================
Menghapus background & memperbaiki kualitas tinta tanda tangan digital.

Pipeline:
  1. cv2.cvtColor(BGR2GRAY)        → Grayscale
  2. cv2.medianBlur                 → Noise reduction (preserve edges)
  3. cv2.threshold(THRESH_OTSU)     → Auto binary threshold
  4. cv2.morphologyEx(MORPH_OPEN)   → Hapus noise titik-titik kecil
  5. cv2.morphologyEx(MORPH_CLOSE)  → Sambung tinta putus 1px
  6. cv2.GaussianBlur pada mask     → Anti-aliasing smooth edges
  7. Alpha channel                  → Tinta hitam opaque, bg transparan
  8. Auto-crop                     → Buang whitespace berlebih

Endpoints:
  POST /process        → Proses 1 TTD (default medium)
  POST /process/batch  → Proses banyak TTD sekaligus
  POST /analyze        → Analisis metrik TTD tanpa proses
  GET  /health         → Health check
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import cv2
import numpy as np
import base64
import time
from datetime import datetime

app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}})


# ================================================================
#  UTILITAS
# ================================================================

def auto_crop_alpha(img_rgba):
    """
    Crop area transparan berlebih di sekeliling tinta.
    Sama seperti ImageMagick -trim.
    """
    if img_rgba.shape[2] < 4:
        return img_rgba

    alpha = img_rgba[:, :, 3]
    coords = cv2.findNonZero(alpha)
    if coords is None:
        return img_rgba

    x, y, w, h = cv2.boundingRect(coords)

    # Padding 15px biar nggak terlalu nempel ke tepi
    pad = 15
    x1 = max(0, x - pad)
    y1 = max(0, y - pad)
    x2 = min(img_rgba.shape[1], x + w + pad)
    y2 = min(img_rgba.shape[0], y + h + pad)

    return img_rgba[y1:y2, x1:x2]


def get_otsu_threshold(gray):
    """
    Hitung Otsu threshold manual (seperti cv2.THRESH_OTSU)
    tapi kita juga return rasio hitam/putih untuk deteksi gagal.
    """
    otsu_val, binary = cv2.threshold(
                gray,
                0,
                255,
                cv2.THRESH_BINARY_INV + cv2.THRESH_OTSU
            )
    black_ratio = np.sum(binary == 255) / binary.size
    return binary, int(otsu_val), black_ratio


def smart_threshold(blurred):
    """
    Thresholding cerdas:
    - Coba Otsu dulu
    - Jika rasio hitam > 65% atau < 1.5%, fallback ke Adaptive
    - Return binary mask (tinta = 255, bg = 0)
    """
    binary, otsu_val, black_ratio = get_otsu_threshold(blurred)

    # Otsu gagal: terlalu banyak hitam atau terlalu sedikit
    if black_ratio > 0.65 or black_ratio < 0.015:
        binary = cv2.adaptiveThreshold(
            blurred, 255,
            cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
            cv2.THRESH_BINARY_INV,
            blockSize=15,
            C=5
        )
        black_ratio = np.sum(binary == 255) / binary.size

    return binary, black_ratio


# ================================================================
#  PIPELINE UTAMA
# ================================================================

def process_signature(image_bgr):
    """
    Pipeline default — setara dengan yang ada di JS tapi lebih baik
    karena pakai OpenCV median blur (preserve edges) dan proper
    morphological kernels.

    Parameters:
        image_bgr: numpy array BGR dari cv2.imdecode

    Returns:
        numpy array RGBA (tinta hitam, bg transparan), sudah auto-crop
    """
    h, w = image_bgr.shape[:2]

    # 1. Grayscale
    gray = cv2.cvtColor(image_bgr, cv2.COLOR_BGR2GRAY)

    # 2. Median blur kernel=3 — hapus noise tanpa blur edges
    #    (di JS cuma pakai box blur 3x3 yang justru blur edges)
    blurred = cv2.medianBlur(gray, 3)

    # 3. Smart thresholding (Otsu + fallback Adaptive)
    binary, black_ratio = smart_threshold(blurred)

    # 4. MORPH_OPEN — hapus noise titik-titik kecil
    #    Kernel 2x2 supaya nggak terlalu agresif (di JS pakai 3x3)
    kernel_open = np.ones((2, 2), np.uint8)
    opened = cv2.morphologyEx(binary, cv2.MORPH_OPEN, kernel_open)

    # 5. MORPH_CLOSE — sambung tinta putus
    #    Kernel 3x3 = maksimal gap 1px (sama seperti JS)
    kernel_close = np.ones((3, 3), np.uint8)
    closed = cv2.morphologyEx(opened, cv2.MORPH_CLOSE, kernel_close)

    # 6. Edge refinement — Gaussian blur kecil pada mask lalu threshold lagi
    #    Ini menghasilkan anti-aliased edges yang halus
    #    (di JS tidak ada langkah ini)
    smooth_mask = cv2.GaussianBlur(closed.astype(np.float32), (3, 3), 0)
    refined = (smooth_mask > 127).astype(np.uint8) * 255

    # 7. Buat RGBA: tinta = hitam opaque, bg = transparan
    result = np.zeros((h, w, 4), dtype=np.uint8)
    result[:, :, 3] = refined  # Alpha channel saja, RGB tetap 0 (hitam)

    # 8. Auto-crop — buang whitespace transparan berlebih
    result = auto_crop_alpha(result)

    return result


def process_signature_enhanced(image_bgr, options=None):
    """
    Pipeline enhanced dengan parameter kontrol.

    Options dict:
        strength:     'light' | 'medium' | 'heavy'   (default: 'medium')
        thickness:    'thin'  | 'normal' | 'bold'    (default: 'normal')
        smooth_edges: True | False                      (default: True)
        auto_crop:    True | False                      (default: True)
        ink_color:    [B, G, R] array                  (default: [0,0,0] hitam)
    """
    if options is None:
        options = {}

    strength = options.get('strength', 'medium')
    thickness = options.get('thickness', 'normal')
    smooth_edges = options.get('smooth_edges', True)
    do_crop = options.get('auto_crop', True)
    ink_color = options.get('ink_color', [0, 0, 0])  # BGR

    h, w = image_bgr.shape[:2]

    # --- 1. Grayscale ---
    gray = cv2.cvtColor(image_bgr, cv2.COLOR_BGR2GRAY)

    # --- 2. Noise reduction berdasarkan strength ---
    if strength == 'light':
        blurred = cv2.medianBlur(gray, 3)
    elif strength == 'medium':
        blurred = cv2.medianBlur(gray, 5)
        # Tambahan Gaussian ringan setelah median
        blurred = cv2.GaussianBlur(blurred, (3, 3), 0)
    else:  # heavy
        blurred = cv2.medianBlur(gray, 7)
        blurred = cv2.GaussianBlur(blurred, (5, 5), 1.0)

    # --- 3. Smart thresholding ---
    binary, black_ratio = smart_threshold(blurred)

    # --- 4. Morphological operations berdasarkan strength ---
    kernels = {
        'light':  (np.ones((2, 2), np.uint8), np.ones((2, 2), np.uint8)),
        'medium': (np.ones((2, 2), np.uint8), np.ones((3, 3), np.uint8)),
        'heavy':  (np.ones((3, 3), np.uint8), np.ones((4, 4), np.uint8)),
    }
    k_open, k_close = kernels.get(strength, kernels['medium'])

    result = cv2.morphologyEx(binary, cv2.MORPH_OPEN, k_open)
    result = cv2.morphologyEx(result, cv2.MORPH_CLOSE, k_close)

    # --- 5. Thickness adjustment ---
    if thickness == 'thin':
        k_thin = np.ones((2, 2), np.uint8)
        result = cv2.erode(result, k_thin, iterations=1)
    elif thickness == 'bold':
        k_bold = np.ones((2, 2), np.uint8)
        result = cv2.dilate(result, k_bold, iterations=1)
    # 'normal' = tidak diubah

    # --- 6. Smooth edges ---
    if smooth_edges:
        smooth = cv2.GaussianBlur(result.astype(np.float32), (3, 3), 0)
        result = (smooth > 127).astype(np.uint8) * 255

    # --- 7. Buat RGBA dengan warna tinta custom ---
    rgba = np.zeros((h, w, 4), dtype=np.uint8)
    # Warnai tinta sesuai ink_color
    mask = result > 0
    rgba[:, :, 0] = np.where(mask, ink_color[0], 0)  # B
    rgba[:, :, 1] = np.where(mask, ink_color[1], 0)  # G
    rgba[:, :, 2] = np.where(mask, ink_color[2], 0)  # R
    rgba[:, :, 3] = result  # Alpha

    # --- 8. Auto crop ---
    if do_crop:
        rgba = auto_crop_alpha(rgba)

    return rgba


# ================================================================
#  ENDPOINTS
# ================================================================

@app.route('/process', methods=['POST'])
def process_ttd():
    """
    Proses 1 gambar tanda tangan.

    Body JSON:
        image:   base64 string (boleh dengan/tanpa "data:image/..." prefix)
        options: object (opsional) — lihat process_signature_enhanced()

    Response:
        success: true/false
        image:   data URL PNG hasil proses
        info:    { width, height, ink_percentage, ink_pixels, processing_time_ms }
    """
    try:
        data = request.json
        image_data = data.get('image', '')
        options = data.get('options', None)

        # Hilangkan prefix "data:image/png;base64," jika ada
        if ',' in image_data:
            image_data = image_data.split(',')[1]

        # Decode base64 → numpy array → BGR image
        img_bytes = base64.b64decode(image_data)
        nparr = np.frombuffer(img_bytes, np.uint8)
        frame = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if frame is None:
            return jsonify({
                'success': False,
                'message': 'Gagal mendekode gambar. Pastikan format benar (PNG/JPG).'
            }), 400

        start_time = time.time()

        # Pilih pipeline
        if options and isinstance(options, dict) and len(options) > 0:
            result = process_signature_enhanced(frame, options)
        else:
            result = process_signature(frame)

        processing_time = round((time.time() - start_time) * 1000, 2)

        # Encode hasil ke PNG
        _, buffer = cv2.imencode('.png', result)
        result_b64 = base64.b64encode(buffer).decode('utf-8')

        # Statistik tinta
        alpha = result[:, :, 3]
        ink_pixels = int(np.sum(alpha > 0))
        total_pixels = result.shape[0] * result.shape[1]
        ink_pct = round(ink_pixels / total_pixels * 100, 2) if total_pixels > 0 else 0

        return jsonify({
            'success': True,
            'image': 'data:image/png;base64,' + result_b64,
            'info': {
                'width': result.shape[1],
                'height': result.shape[0],
                'ink_percentage': ink_pct,
                'ink_pixels': ink_pixels,
                'processing_time_ms': processing_time,
                'pipeline': 'enhanced' if options else 'default'
            },
            'timestamp': time.time()
        })

    except Exception as e:
        print(f"[ERROR] /process: {e}")
        import traceback
        traceback.print_exc()
        return jsonify({
            'success': False,
            'message': f'Server error: {str(e)}'
        }), 500


@app.route('/process/batch', methods=['POST'])
def process_batch():
    """
    Proses banyak gambar sekaligus.

    Body JSON:
        images:  array of base64 strings
        options: object (opsional, diterapkan ke semua gambar)

    Response:
        success: true/false
        results: array of { index, success, image, width, height }
        total_time_ms: total waktu pemrosesan
    """
    try:
        data = request.json
        images = data.get('images', [])
        options = data.get('options', None)

        if not images or not isinstance(images, list):
            return jsonify({
                'success': False,
                'message': 'Field "images" harus berupa array base64 string.'
            }), 400

        if len(images) > 50:
            return jsonify({
                'success': False,
                'message': 'Maksimal 50 gambar per request.'
            }), 400

        results = []
        start_time = time.time()

        for idx, img_data in enumerate(images):
            try:
                if ',' in img_data:
                    img_data = img_data.split(',')[1]

                img_bytes = base64.b64decode(img_data)
                nparr = np.frombuffer(img_bytes, np.uint8)
                frame = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

                if frame is None:
                    results.append({
                        'index': idx,
                        'success': False,
                        'message': 'Gagal decode gambar'
                    })
                    continue

                if options and isinstance(options, dict) and len(options) > 0:
                    result = process_signature_enhanced(frame, options)
                else:
                    result = process_signature(frame)

                _, buffer = cv2.imencode('.png', result)
                result_b64 = base64.b64encode(buffer).decode('utf-8')

                results.append({
                    'index': idx,
                    'success': True,
                    'image': 'data:image/png;base64,' + result_b64,
                    'width': result.shape[1],
                    'height': result.shape[0]
                })

            except Exception as e:
                results.append({
                    'index': idx,
                    'success': False,
                    'message': str(e)
                })

        total_time = round((time.time() - start_time) * 1000, 2)

        return jsonify({
            'success': True,
            'results': results,
            'total_time_ms': total_time,
            'count': len(results)
        })

    except Exception as e:
        print(f"[ERROR] /process/batch: {e}")
        return jsonify({
            'success': False,
            'message': f'Server error: {str(e)}'
        }), 500


@app.route('/analyze', methods=['POST'])
def analyze_ttd():
    """
    Analisis metrik gambar TTD tanpa memproses.
    Berguna untuk debugging / menentukan parameter yang tepat.

    Response:
        analysis: {
            width, height, channels,
            mean_brightness, std_brightness,
            otsu_threshold, ink_percentage,
            white_bg_percentage, sharpness,
            likely_has_white_bg, likely_has_transparent_bg,
            recommendation: saran strength/thickness
        }
    """
    try:
        data = request.json
        image_data = data.get('image', '')

        if ',' in image_data:
            image_data = image_data.split(',')[1]

        img_bytes = base64.b64decode(image_data)
        nparr = np.frombuffer(img_bytes, np.uint8)
        frame = cv2.imdecode(nparr, cv2.IMREAD_UNCHANGED)

        if frame is None:
            return jsonify({
                'success': False,
                'message': 'Gagal mendekode gambar.'
            }), 400

        has_alpha = frame.shape[2] == 4 if len(frame.shape) == 3 else False

        # Pakai BGR untuk analisis (abaikan alpha)
        if has_alpha:
            bgr = frame[:, :, :3]
        else:
            bgr = frame

        gray = cv2.cvtColor(bgr, cv2.COLOR_BGR2GRAY)

        # Metrik brightness
        mean_bright = round(float(np.mean(gray)), 2)
        std_bright = round(float(np.std(gray)), 2)

        # Otsu
        otsu_val, binary = cv2.threshold(
            gray,
            0,
            255,
            cv2.THRESH_BINARY_INV + cv2.THRESH_OTSU
        )
        ink_ratio = round(float(np.sum(binary == 255)) / binary.size * 100, 2)

        # White background detection
        white_pct = round(float(np.sum(gray > 240)) / gray.size * 100, 2)

        # Sharpness (Laplacian variance)
        sharpness = round(float(cv2.Laplacian(gray, cv2.CV_64F).var()), 2)

        # Transparan detection
        has_transparent = False
        if has_alpha:
            alpha_mean = float(np.mean(frame[:, :, 3]))
            has_transparent = alpha_mean < 200

        # Rekomendasi
        recommendation = {}
        if ink_ratio > 15:
            recommendation['strength'] = 'light'
        elif ink_ratio > 5:
            recommendation['strength'] = 'medium'
        else:
            recommendation['strength'] = 'heavy'

        if sharpness > 500:
            recommendation['thickness'] = 'thin'
        elif sharpness > 100:
            recommendation['thickness'] = 'normal'
        else:
            recommendation['thickness'] = 'bold'

        if std_bright > 60:
            recommendation['note'] = 'Gambar punya variasi brightness tinggi, kemungkinan ada noise. Gunakan strength medium-heavy.'

        return jsonify({
            'success': True,
            'analysis': {
                'width': bgr.shape[1],
                'height': bgr.shape[0],
                'channels': bgr.shape[2] if len(bgr.shape) == 3 else 1,
                'has_alpha': has_alpha,
                'mean_brightness': mean_bright,
                'std_brightness': std_bright,
                'otsu_threshold': int(otsu_val),
                'ink_percentage': ink_ratio,
                'white_bg_percentage': white_pct,
                'sharpness': sharpness,
                'likely_has_white_bg': white_pct > 50,
                'likely_has_transparent_bg': has_transparent,
                'recommendation': recommendation
            },
            'timestamp': time.time()
        })

    except Exception as e:
        print(f"[ERROR] /analyze: {e}")
        return jsonify({
            'success': False,
            'message': f'Server error: {str(e)}'
        }), 500


@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint."""
    return jsonify({
        'status': 'healthy',
        'service': 'ttd-processing',
        'version': '1.0.0',
        'opencv_version': cv2.__version__,
        'numpy_version': np.__version__,
        'timestamp': datetime.now().isoformat()
    })


# ================================================================
#  MAIN
# ================================================================

if __name__ == '__main__':
    print("=" * 55)
    print("  TTD Processing Service (OpenCV Python)")
    print("=" * 55)
    print(f"  OpenCV Version : {cv2.__version__}")
    print(f"  NumPy Version  : {np.__version__}")
    print(f"  Port           : 5001")
    print("-" * 55)
    print("  Endpoints:")
    print("    POST /process        Proses 1 TTD")
    print("    POST /process/batch  Proses banyak TTD")
    print("    POST /analyze        Analisis metrik TTD")
    print("    GET  /health         Health check")
    print("=" * 55)
    print()

    app.run(host='0.0.0.0', port=5001, debug=False, threaded=True)