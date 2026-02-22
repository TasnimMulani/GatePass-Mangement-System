// OCR Handler for ID Card Scanning using Tesseract.js

let ocrWorker = null;

// Initialize Tesseract Worker
async function initOCR() {
    if (!ocrWorker) {
        try {
            // Updated for Tesseract.js v4+ compatibility
            ocrWorker = await Tesseract.createWorker({
                logger: m => {
                    const statusText = document.getElementById('ocr-status-text');
                    if (statusText && m.status === 'recognizing text') {
                        statusText.textContent = `Analyzing ID: ${Math.round(m.progress * 100)}%`;
                    }
                }
            });
            await ocrWorker.loadLanguage('eng');
            await ocrWorker.initialize('eng');
            console.log('OCR Worker initialized and ready');
        } catch (error) {
            console.error('Failed to initialize OCR:', error);
            showNotification('Failed to initialize OCR engine', 'danger');
            ocrWorker = null;
        }
    }
    return ocrWorker;
}

// Preprocess Image for better OCR (Grayscale & Contrast)
async function preprocessImage(file) {
    console.log('Preprocessing image...', file.name);
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                try {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = img.width;
                    canvas.height = img.height;

                    // Draw original image
                    ctx.drawImage(img, 0, 0);

                    // Get image data
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const data = imageData.data;

                    // Apply Grayscale and Contrast enhancement
                    const contrast = 1.2;
                    const intercept = 128 * (1 - contrast);

                    for (let i = 0; i < data.length; i += 4) {
                        // Grayscale
                        const avg = (data[i] + data[i + 1] + data[i + 2]) / 3;
                        // Contrast
                        const newVal = avg * contrast + intercept;

                        data[i] = newVal; data[i + 1] = newVal; data[i + 2] = newVal;
                    }

                    ctx.putImageData(imageData, 0, 0);

                    // Store processed image in hidden input for backend
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                    const photoInput = document.getElementById('photo-data');
                    if (photoInput) photoInput.value = dataUrl;

                    console.log('Preprocessing complete');
                    resolve(dataUrl);
                } catch (err) {
                    console.error('Preprocessing error:', err);
                    reject(err);
                }
            };
            img.onerror = (err) => {
                console.error('Image load error:', err);
                reject(err);
            };
            img.src = e.target.result;
        };
        reader.onerror = (err) => {
            console.error('FileReader error:', err);
            reject(err);
        };
        reader.readAsDataURL(file);
    });
}

// Process ID Card Image
async function processIDCard(file) {
    if (typeof Tesseract === 'undefined') {
        showNotification('OCR engine (Tesseract) not loaded. Please check your internet connection.', 'danger');
        return;
    }

    const loadingAlert = document.getElementById('ocr-loading');
    const statusText = document.getElementById('ocr-status-text');
    const resultDiv = document.getElementById('ocr-result');

    if (loadingAlert) loadingAlert.style.display = 'block';
    if (statusText) statusText.textContent = 'Preprocessing image...';
    if (resultDiv) resultDiv.style.display = 'none';

    try {
        // 1. Preprocess and capture for photo record
        const processedImageUrl = await preprocessImage(file);

        // 2. Initialize OCR
        if (statusText) statusText.textContent = 'Initializing AI engine...';
        const worker = await initOCR();

        // 3. Perform OCR on processed image
        if (statusText) statusText.textContent = 'Analyzing document...';
        const { data: { text } } = await worker.recognize(processedImageUrl);
        console.log('OCR Output:', text);

        // 4. Parse the extracted text
        const extractedData = parseIDCardText(text);

        // 5. Fill form fields
        fillFormFields(extractedData);

        if (loadingAlert) loadingAlert.style.display = 'none';
        if (resultDiv) {
            resultDiv.style.display = 'block';
            let statusText = `Auto-filled: ${Object.keys(extractedData).filter(k => extractedData[k] && k !== 'id_type').join(', ')}`;
            if (extractedData.id_type) statusText += `<br>Detected: <strong>${extractedData.id_type}</strong>`;

            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <strong>✓ ID Card Scanned & Processed!</strong><br>
                    ${statusText}
                </div>
            `;
        }

        showNotification('ID Card processed successfully!', 'success');

    } catch (error) {
        console.error('OCR Error:', error);
        if (loadingAlert) loadingAlert.style.display = 'none';
        showNotification('Failed to process ID card. Please enter details manually.', 'danger');
    }
}

// Parse extracted text with enhanced pattern matching and classification
function parseIDCardText(text) {
    const data = {
        full_name: '',
        identity_card_no: '',
        contact_number: '',
        email: '',
        id_type: ''
    };

    const lines = text.split('\n').map(line => line.trim()).filter(line => line);
    const upperText = text.toUpperCase();

    // 1. Document Classification
    if (upperText.includes('GOVERNMENT OF INDIA') || upperText.includes('UNIQUE IDENTIFICATION')) {
        data.id_type = 'Aadhar Card';
    } else if (upperText.includes('INCOME TAX DEPARTMENT') || upperText.includes('PERMANENT ACCOUNT NUMBER')) {
        data.id_type = 'PAN Card';
    } else if (upperText.includes('ELECTION COMMISSION') || upperText.includes('VOTER')) {
        data.id_type = 'Voter ID';
    } else if (upperText.includes('DRIVING LICENCE') || upperText.includes('TRANSPORT DEPARTMENT')) {
        data.id_type = 'Driving License';
    }

    // 2. ID Number Extraction
    // Aadhar (12 digits)
    const aadharPattern = /\b\d{4}\s?\d{4}\s?\d{4}\b/;
    const aadharMatch = text.match(aadharPattern);
    if (aadharMatch) {
        data.identity_card_no = aadharMatch[0].replace(/\s/g, '');
        if (!data.id_type) data.id_type = 'Aadhar Card';
    }

    // PAN (ABCDE1234F)
    const panPattern = /[A-Z]{5}\d{4}[A-Z]/;
    const panMatch = text.match(panPattern);
    if (panMatch && !data.identity_card_no) {
        data.identity_card_no = panMatch[0];
        if (!data.id_type) data.id_type = 'PAN Card';
    }

    // Voter ID (3 letters + 7 digits)
    const voterPattern = /[A-Z]{3}\d{7}/;
    const voterMatch = text.match(voterPattern);
    if (voterMatch && !data.identity_card_no) {
        data.identity_card_no = voterMatch[0];
        if (!data.id_type) data.id_type = 'Voter ID';
    }

    // Driving License (State Code + 13 digits)
    const dlPattern = /[A-Z]{2}\d{13}/;
    const dlMatch = text.match(dlPattern);
    if (dlMatch && !data.identity_card_no) {
        data.identity_card_no = dlMatch[0];
        if (!data.id_type) data.id_type = 'Driving License';
    }

    // 3. Contact & Email
    const phonePattern = /\b[6-9]\d{9}\b/;
    const phoneMatch = text.match(phonePattern);
    if (phoneMatch) data.contact_number = phoneMatch[0];

    const emailPattern = /\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/;
    const emailMatch = text.match(emailPattern);
    if (emailMatch) data.email = emailMatch[0];

    // 4. Name Extraction (Improved Heuristics for Gaps & Labels)
    const skipWords = [
        'INDIA', 'GOVERNMENT', 'DATE', 'BIRTH', 'GENDER', 'INCOME', 'TAX', 'DEPARTMENT',
        'ELECTION', 'COMMISSION', 'ADDRESS', 'MALE', 'FEMALE', 'YEAR', 'POSTAL', 'PIN',
        'MOBILE', 'PHONE', 'ACCOUNT', 'NUMBER', 'DIGIT', 'ENROLMENT', 'VID', 'FATHER', 'MOTHER', 'HUSBAND'
    ];

    // First, look for lines with "Name" label
    for (let i = 0; i < lines.length; i++) {
        const line = lines[i].toUpperCase();
        const originalLine = lines[i];

        // Explicitly check for Name but strictly avoid Father's/Mother's Name labels
        if (line.includes('NAME') && !line.includes('FATHER') && !line.includes('MOTHER') && !line.includes('HUSBAND')) {
            // Check for names on the same line, even with large gaps
            // Split by common separators like colons, or look for large gaps (2+ spaces)
            const parts = originalLine.split(/[:\s]{2,}/);

            if (parts.length > 1) {
                for (let p = 1; p < parts.length; p++) {
                    const candidate = parts[p].trim();
                    if (candidate.length > 3 && candidate.length < 40 && !skipWords.some(word => candidate.toUpperCase().includes(word))) {
                        data.full_name = candidate;
                        break;
                    }
                }
            }

            if (data.full_name) break;

            // If not found on same line, look at the immediate NEXT line
            if (i + 1 < lines.length) {
                const nextLine = lines[i + 1].trim();
                if (nextLine.length > 3 && nextLine.length < 40 && !skipWords.some(word => nextLine.toUpperCase().includes(word))) {
                    data.full_name = nextLine;
                    break;
                }
            }
        }
    }

    // Heuristic 2: Look for 2-4 word sequences (Title Case or UPPER CASE)
    // Often names are prominent lines without labels
    if (!data.full_name) {
        const titleCasePattern = /^[A-Z][a-z]+(\s[A-Z][a-z]+){1,3}$/;
        const upperCasePattern = /^[A-Z]{3,}(\s[A-Z]{3,}){1,2}$/;

        for (const line of lines) {
            const cleanLine = line.replace(/[^A-Za-z\s]/g, '').trim();
            if ((titleCasePattern.test(cleanLine) || upperCasePattern.test(cleanLine)) && cleanLine.length > 5 && cleanLine.length < 40) {
                // Stronger filtering: Ensure it doesn't contain ANY skip words
                if (!skipWords.some(word => cleanLine.toUpperCase().includes(word))) {
                    data.full_name = cleanLine;
                    break;
                }
            }
        }
    }

    // Heuristic 3: Aadhar special (often name is just above the Aadhar number or below "Government of India")
    if (!data.full_name && lines.length > 0) {
        for (let i = 0; i < Math.min(6, lines.length); i++) {
            const cleanLine = lines[i].replace(/[^A-Za-z\s]/g, '').trim();
            if (cleanLine.split(' ').length >= 2 && cleanLine.length > 6 && cleanLine.length < 35) {
                if (!skipWords.some(word => cleanLine.toUpperCase().includes(word))) {
                    // Check if it's not a common header
                    if (!['GOVERNMENT OF INDIA', 'UNIQUE IDENTIFICATION'].includes(cleanLine.toUpperCase())) {
                        data.full_name = cleanLine;
                        break;
                    }
                }
            }
        }
    }

    return data;
}

// Fill form fields with extracted data
function fillFormFields(data) {
    const fieldMapping = {
        'full_name': 'full_name',
        'identity_card_no': 'identity_card_no',
        'contact_number': 'contact_number',
        'email': 'email',
        'id_type': 'identity_type'
    };

    for (const [dataKey, fieldId] of Object.entries(fieldMapping)) {
        if (data[dataKey]) {
            const field = document.getElementById(fieldId) || document.querySelector(`[name="${fieldId}"]`);
            if (field) {
                field.value = data[dataKey];
                field.style.borderColor = 'var(--success)';
                field.style.boxShadow = '0 0 0 2px rgba(34, 197, 94, 0.2)';
                setTimeout(() => {
                    field.style.borderColor = '';
                    field.style.boxShadow = '';
                }, 3000);
            }
        }
    }
}

// Setup file input handler
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('id-card-upload');
    const uploadSection = document.getElementById('ocr-upload-section');

    if (uploadSection && fileInput) {
        uploadSection.addEventListener('click', () => fileInput.click());

        uploadSection.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadSection.style.borderColor = 'var(--accent-secondary)';
            uploadSection.style.background = 'rgba(99, 102, 241, 0.15)';
        });

        uploadSection.addEventListener('dragleave', () => {
            uploadSection.style.borderColor = '';
            uploadSection.style.background = '';
        });

        uploadSection.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadSection.style.borderColor = '';
            uploadSection.style.background = '';

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                if (files[0].type.startsWith('image/')) {
                    processIDCard(files[0]);
                } else {
                    showNotification('Please upload an image file', 'danger');
                }
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files[0]) processIDCard(e.target.files[0]);
        });
    }
});
