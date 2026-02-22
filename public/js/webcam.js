// webcam.js - Simple webcam capture functionality

class WebcamCapture {
    constructor(videoElement, canvasElement) {
        this.video = videoElement;
        this.canvas = canvasElement;
        this.stream = null;
    }

    async start() {
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            });
            this.video.srcObject = this.stream;
            this.video.play();
            return true;
        } catch (error) {
            console.error('Error accessing webcam:', error);
            alert('Unable to access webcam. Please ensure you have granted camera permissions.');
            return false;
        }
    }

    capture() {
        const context = this.canvas.getContext('2d');
        this.canvas.width = this.video.videoWidth;
        this.canvas.height = this.video.videoHeight;
        context.drawImage(this.video, 0, 0);

        // Return base64 image data
        return this.canvas.toDataURL('image/jpeg', 0.8);
    }

    stop() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.video.srcObject = null;
        }
    }
}

// Global webcam instance
let webcam = null;

function initWebcam() {
    const video = document.getElementById('webcam-video');
    const canvas = document.getElementById('webcam-canvas');
    webcam = new WebcamCapture(video, canvas);
}

function startCamera() {
    if (!webcam) {
        initWebcam();
    }

    const cameraSection = document.getElementById('camera-section');
    const captureButton = document.getElementById('capture-photo-btn');

    webcam.start().then(success => {
        if (success) {
            cameraSection.style.display = 'block';
            captureButton.disabled = false;
        }
    });
}

function capturePhoto() {
    if (!webcam) return;

    const imageData = webcam.capture();
    const preview = document.getElementById('photo-preview');
    const photoInput = document.getElementById('photo-data');

    // Display preview
    preview.src = imageData;
    preview.style.display = 'block';

    const placeholder = document.getElementById('photo-placeholder');
    if (placeholder) placeholder.style.display = 'none';

    // Store in hidden input for form submission
    photoInput.value = imageData;

    // Stop camera
    webcam.stop();
    document.getElementById('camera-section').style.display = 'none';

    showNotification('Photo captured successfully!', 'success');
}

function retakePhoto() {
    document.getElementById('photo-preview').style.display = 'none';
    const placeholder = document.getElementById('photo-placeholder');
    if (placeholder) placeholder.style.display = 'block';
    document.getElementById('photo-data').value = '';
    startCamera();
}
