// OCR Handler for ID Card Scanning using Tesseract.js

let ocrWorker = null;

// Initialize Tesseract Worker
async function initOCR() {
    if (!ocrWorker) {
        try {
            ocrWorker = await Tesseract.createWorker('eng');
            console.log('OCR Worker initialized');
        } catch (error) {
            console.error('Failed to initialize OCR:', error);
            showNotification('Failed to initialize OCR engine', 'danger');
        }
    }
    return ocrWorker;
}

// Process ID Card Image
async function processIDCard(file) {
    const loadingAlert = document.getElementById('ocr-loading');
    const resultDiv = document.getElementById('ocr-result');

    if (loadingAlert) loadingAlert.style.display = 'block';
    if (resultDiv) resultDiv.style.display = 'none';

    try {
        // Initialize OCR if not already done
        const worker = await initOCR();

        // Create image URL from file
        const imageUrl = URL.createObjectURL(file);

        // Perform OCR
        const { data: { text } } = await worker.recognize(imageUrl);

        // Parse the extracted text
        const extractedData = parseIDCardText(text);

        // Fill form fields
        fillFormFields(extractedData);

        // Clean up
        URL.revokeObjectURL(imageUrl);

        if (loadingAlert) loadingAlert.style.display = 'none';
        if (resultDiv) {
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <strong>✓ ID Card Scanned Successfully!</strong><br>
                    Auto-filled: ${Object.keys(extractedData).filter(k => extractedData[k]).join(', ')}
                </div>
            `;
        }

        showNotification('ID Card scanned successfully!', 'success');

    } catch (error) {
        console.error('OCR Error:', error);
        if (loadingAlert) loadingAlert.style.display = 'none';
        showNotification('Failed to process ID card. Please enter details manually.', 'danger');
    }
}

// Parse extracted text to find relevant information
function parseIDCardText(text) {
    const data = {
        full_name: '',
        identity_card_no: '',
        contact_number: '',
        email: ''
    };

    // Clean text
    const lines = text.split('\n').map(line => line.trim()).filter(line => line);

    // Pattern matching for different ID types

    // Aadhar Card Number (12 digits, may have spaces)
    const aadharPattern = /\b\d{4}\s?\d{4}\s?\d{4}\b/;
    const aadharMatch = text.match(aadharPattern);
    if (aadharMatch) {
        data.identity_card_no = aadharMatch[0].replace(/\s/g, '');
    }

    // PAN Card Pattern (ABCDE1234F)
    const panPattern = /[A-Z]{5}\d{4}[A-Z]/;
    const panMatch = text.match(panPattern);
    if (panMatch && !data.identity_card_no) {
        data.identity_card_no = panMatch[0];
    }

    // Phone Number (10 digits)
    const phonePattern = /\b[6-9]\d{9}\b/;
    const phoneMatch = text.match(phonePattern);
    if (phoneMatch) {
        data.contact_number = phoneMatch[0];
    }

    // Email Pattern
    const emailPattern = /\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/;
    const emailMatch = text.match(emailPattern);
    if (emailMatch) {
        data.email = emailMatch[0];
    }

    // Name Extraction (heuristic: look for lines with 2-3 words in title case)
    const namePattern = /^[A-Z][a-z]+(\s[A-Z][a-z]+){1,2}$/;
    for (const line of lines) {
        if (namePattern.test(line) && !data.full_name) {
            data.full_name = line;
            break;
        }
    }

    // If no name found using pattern, try to find it in common positions
    if (!data.full_name && lines.length > 0) {
        // Often name is in first few lines
        for (let i = 0; i < Math.min(3, lines.length); i++) {
            if (lines[i].length > 3 && lines[i].length < 50 && /[A-Za-z]/.test(lines[i])) {
                data.full_name = lines[i];
                break;
            }
        }
    }

    return data;
}

// Fill form fields with extracted data
function fillFormFields(data) {
    if (data.full_name) {
        const nameField = document.getElementById('full_name') || document.querySelector('[name="full_name"]');
        if (nameField) {
            nameField.value = data.full_name;
            nameField.style.borderColor = 'var(--success)';
            setTimeout(() => nameField.style.borderColor = '', 2000);
        }
    }

    if (data.identity_card_no) {
        const idField = document.getElementById('identity_card_no') || document.querySelector('[name="identity_card_no"]');
        if (idField) {
            idField.value = data.identity_card_no;
            idField.style.borderColor = 'var(--success)';
            setTimeout(() => idField.style.borderColor = '', 2000);
        }
    }

    if (data.contact_number) {
        const phoneField = document.getElementById('contact_number') || document.querySelector('[name="contact_number"]');
        if (phoneField) {
            phoneField.value = data.contact_number;
            phoneField.style.borderColor = 'var(--success)';
            setTimeout(() => phoneField.style.borderColor = '', 2000);
        }
    }

    if (data.email) {
        const emailField = document.getElementById('email') || document.querySelector('[name="email"]');
        if (emailField) {
            emailField.value = data.email;
            emailField.style.borderColor = 'var(--success)';
            setTimeout(() => emailField.style.borderColor = '', 2000);
        }
    }
}

// Setup file input handler
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('id-card-upload');
    const uploadSection = document.getElementById('ocr-upload-section');

    if (uploadSection && fileInput) {
        // Click to upload
        uploadSection.addEventListener('click', () => fileInput.click());

        // Drag and drop
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
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    processIDCard(file);
                } else {
                    showNotification('Please upload an image file', 'danger');
                }
            }
        });

        // File input change
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                processIDCard(file);
            }
        });
    }
});
