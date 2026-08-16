#!/bin/bash

echo "Installing dependencies..."
pip install -r requirements.txt

echo "Starting Face Detection Service..."
python face_service.py

#!/bin/bash

echo "========================================"
echo "  Starting TTD Processing Service"
echo "========================================"

# Cek apakah dependencies sudah terinstall
echo "[1/2] Checking dependencies..."
python -c "import cv2; import numpy; import flask; import flask_cors" 2>/dev/null

if [ $? -ne 0 ]; then
    echo "[!] Missing dependencies. Installing..."
    pip install flask==3.0.0 flask-cors==4.0.0 opencv-python==4.8.1.78 numpy==1.24.3 Pillow==10.1.0
else
    echo "[OK] All dependencies available."
fi

echo "[2/2] Starting TTD service on port 5001..."
python ttd_service.py