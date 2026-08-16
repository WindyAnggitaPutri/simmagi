"""
Face Detection Service (Tanpa Deteksi Mata)
Cuma screening: deteksi wajah, cek kualitas, langsung verifikasi
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

# Load Haar Cascade - Hanya face cascade yang dibutuhkan
face_cascade = cv2.CascadeClassifier(
    cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
)


def calculate_face_quality(frame, x, y, w, h):
    """Calculate face image quality score (0-100)"""
    score = 0
    max_score = 100
    
    # Size score (0-30) - wajah harus cukup besar
    face_ratio = (w * h) / (frame.shape[0] * frame.shape[1])
    size_score = min(30, face_ratio * 200)
    score += size_score
    
    # Position score - tengah lebih baik (0-20)
    cx, cy = x + w/2, y + h/2
    fx, fy = frame.shape[1]/2, frame.shape[0]/2
    dist_from_center = np.sqrt((cx-fx)**2 + (cy-fy)**2)
    max_dist = np.sqrt(fx**2 + fy**2)
    position_score = 20 * (1 - dist_from_center/max_dist)
    score += position_score
    
    # Brightness score (0-25)
    roi = frame[y:y+h, x:x+w]
    brightness = np.mean(roi)
    if 80 < brightness < 200:
        brightness_score = 25
    else:
        brightness_score = max(0, 25 - abs(brightness - 140) * 0.25)
    score += brightness_score
    
    # Sharpness score (0-25)
    gray_roi = cv2.cvtColor(roi, cv2.COLOR_BGR2GRAY)
    sharpness = cv2.Laplacian(gray_roi, cv2.CV_64F).var()
    sharpness_score = min(25, sharpness / 50)
    score += sharpness_score
    
    return round(min(max_score, score))


def analyze_frame(frame):
    """Analyze frame - cuma cek wajah & kualitas, langsung verifikasi"""
    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    gray = cv2.equalizeHist(gray)
    
    # Detect faces
    faces = face_cascade.detectMultiScale(
        gray,
        scaleFactor=1.1,
        minNeighbors=5,
        minSize=(100, 100),
        flags=cv2.CASCADE_SCALE_IMAGE
    )
    
    # Tidak ada wajah
    if len(faces) == 0:
        return {
            'face_detected': False,
            'is_live': False,
            'message': 'Wajah tidak terdeteksi. Silakan hadapkan wajah ke kamera.',
            'face_box': None
        }
    
    # Lebih dari 1 wajah - tolak
    if len(faces) > 1:
        return {
            'face_detected': True,
            'is_live': False,
            'message': 'Terdeteksi lebih dari 1 wajah. Pastikan hanya Anda di depan kamera.',
            'face_box': None
        }
    
    # Ambil wajah terbesar
    face = max(faces, key=lambda f: f[2] * f[3])
    x, y, w, h = face
    
    face_box = {
        'x': int(x), 'y': int(y), 
        'w': int(w), 'h': int(h)
    }
    
    # Cek ukuran wajah (terlalu kecil = terlalu jauh / bisa jadi foto)
    face_area = w * h
    frame_area = frame.shape[0] * frame.shape[1]
    face_ratio = face_area / frame_area
    
    if face_ratio < 0.05:
        return {
            'face_detected': True,
            'is_live': False,
            'message': 'Wajah terlalu kecil. Silakan mendekat ke kamera.',
            'face_box': face_box
        }
    
    # ❌ DIHAPUS: Cek deteksi mata - TIDAK DIPERLUKAN LAGI
    
    # Hitung kualitas wajah
    quality = calculate_face_quality(frame, x, y, w, h)
    
    # Kualitas rendah - minta posisi lebih baik
    if quality < 40:
        return {
            'face_detected': True,
            'is_live': False,
            'message': 'Kualitas wajah kurang baik. Pastikan pencahayaan cukup dan wajah jelas.',
            'face_box': face_box,
            'face_quality': quality
        }
    
    # Wajah OK, kualitas cukup -> LANGSUNG VERIFIKASI
    return {
        'face_detected': True,
        'is_live': True,
        'message': 'Wajah terdeteksi. Silakan ambil foto.',
        'face_box': face_box,
        'face_quality': quality
    }


@app.route('/detect', methods=['POST'])
def detect_face():
    """Main endpoint - deteksi wajah tanpa cek mata"""
    try:
        data = request.json
        image_data = data.get('image', '')
        
        # Remove data URL prefix
        if ',' in image_data:
            image_data = image_data.split(',')[1]
        
        # Decode image
        img_bytes = base64.b64decode(image_data)
        nparr = np.frombuffer(img_bytes, np.uint8)
        frame = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        
        if frame is None:
            return jsonify({
                'success': False,
                'message': 'Gagal memproses gambar'
            }), 400
        
        # Analyze
        result = analyze_frame(frame)
        result['success'] = True
        result['timestamp'] = time.time()
        
        return jsonify(result)
    
    except Exception as e:
        print(f"Error in /detect: {e}")
        return jsonify({
            'success': False,
            'message': f'Server error: {str(e)}'
        }), 500


@app.route('/health', methods=['GET'])
def health_check():
    """Health check"""
    return jsonify({
        'status': 'healthy',
        'service': 'face-detection-no-eyes',
        'version': '3.0.0',
        'timestamp': datetime.now().isoformat()
    })


if __name__ == '__main__':
    print("=" * 50)
    print("Face Detection Service (NO EYE CHECK)")
    print("=" * 50)
    print("Starting server on http://0.0.0.0:5000")
    print("Endpoints:")
    print("  POST /detect    - Detect face only")
    print("  GET  /health    - Health check")
    print("=" * 50)
    
    app.run(host='0.0.0.0', port=5000, debug=False, threaded=True)