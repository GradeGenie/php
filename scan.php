<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Assignment | GradeGenie </title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php include 'header.php'; ?>
    <style>
        .hamburger-menu {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000000000000000;
            cursor: pointer;
            font-size: 24px;
        }

        @media screen and (max-width: 768px) {
            .hamburger-menu {
                display: block;
            }
        }
        .menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            z-index: 999;
        }
        .menu-content {
            position: fixed;
            top: 0;
            right: -300px;
            width: 300px;
            height: 100%;
            background-color: #fff;
            transition: right 0.3s ease-in-out;
            z-index: 1000;
            overflow-y: auto;
            padding: 20px;
        }
        .menu-content.active {
            right: 0;
        }
        .close-menu {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
        }
        .menu-content.active {
            background-color: #40444a;
        }
        #video, #photoPreview {
            display: none;
            width: 100%;
            max-width: 640px;
            border-radius: 5px;
        }
        #captureBtn, #startGradingBtn {
            margin-top: 10px;
        }
        #captureBtn{
            background-color: #289efd;
        }
        #result {
            margin-top: 20px;
            line-height: 1.6;
        }
        #result .MathJax {
            font-size: 110%;
        }
        #copyBtn, #emailBtn {
            width: calc(50% - 6px);
            font-size: 12px;
            vertical-align: top;
            margin-bottom: -30px;
            margin-top: 10px;
            color: #2ecc71;
            background: #fff;
            border: 2px solid;
        }
        .close-camera-btn {
    position: absolute;
    top: 215px;
    right: 7px;
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    font-size: 20px;
    cursor: pointer;
    z-index: 10;
    padding: 0;
}
        #video, #photoPreview {
            position: relative;
        }
        .or-divider {
            text-align: center;
            margin: 10px 0;
            color: #666;
        }
        .file-input-label {
            display: block;
            padding: 10px 20px;
            background-color: #f0f0f0;
            color: #333;
            cursor: pointer;
            border-radius: 5px;
            text-align: center;
            margin: 10px auto;
            max-width: 200px;
        }
        .file-input-label:hover {
            background-color: #e0e0e0;
        }
        #scanOptions {
            text-align: center;
        }
    </style>
    <script>
    MathJax = {
        tex: {
            inlineMath: [['$', '$'], ['\\(', '\\)']]
        },
        svg: {
            fontCache: 'global'
        },
        startup: {
            ready: () => {
                console.log('MathJax is loaded and ready');
                MathJax.startup.defaultReady();
            }
        }
    };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js" id="MathJax-script" async></script>
</head>

<body>
    <div class="hamburger-menu" onclick="toggleMenu()">
        <i class="fas fa-bars"></i>
    </div>
    <div class="menu-overlay" onclick="toggleMenu()"></div>
    <div class="menu-content">
        <span class="close-menu" onclick="toggleMenu()">&times;</span>
        <?php include 'menu.php'; ?>
    </div>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <?php include 'registration_modal.php'; ?>
        <script>
            showRegistrationModal();
        </script>
    <?php endif; ?>

    <div id="mainContent">
        <div class="container">
            <h1>Quick Grade Scanner</h1>
            <p>For when assignments are on paper, GradeGenie scans them and grades them.</p>
            <form id="rubricForm">
                <div class="section">
                    <h2>Scan</h2>
                    <div id="scanOptions">
                        <button type="button" id="launchCameraBtn">Launch Camera</button>
                        <div class="or-divider">OR</div>
                        <label for="fileInput" class="file-input-label">Choose an Image</label>
                        <input type="file" id="fileInput" accept="image/*" style="display: none;">
                    </div>
                    <video id="video" autoplay playsinline></video>
                    <img id="photoPreview" alt="Photo preview">
                    <button type="button" id="captureBtn" style="display: none;">Capture Photo</button>
                    <button type="button" id="startGradingBtn" style="display: none;">Start Grading</button>
                    <div id="result"></div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        const video = document.getElementById('video');
        const photoPreview = document.getElementById('photoPreview');
        const launchCameraBtn = document.getElementById('launchCameraBtn');
        const captureBtn = document.getElementById('captureBtn');
        const startGradingBtn = document.getElementById('startGradingBtn');
        const result = document.getElementById('result');
        const fileInput = document.getElementById('fileInput');
        const scanOptions = document.getElementById('scanOptions');
        let stream;

        function resetCamera() {
            video.style.display = 'none';
            photoPreview.style.display = 'none';
            captureBtn.style.display = 'none';
            startGradingBtn.style.display = 'none';
            scanOptions.style.display = 'block';
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        }

        launchCameraBtn.addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                video.srcObject = stream;
                video.style.display = 'block';
                captureBtn.style.display = 'block';
                scanOptions.style.display = 'none';
                
                const closeBtn = document.createElement('button');
                closeBtn.innerHTML = '&times;';
                closeBtn.className = 'close-camera-btn';
                closeBtn.onclick = function(event) {
                    event.preventDefault();
                    resetCamera();
                    this.remove();
                };
                video.parentNode.insertBefore(closeBtn, video);
            } catch (err) {
                console.error('Error accessing camera:', err);
                alert('Error accessing camera. Please make sure you have given permission to use the camera.');
            }
        });

        captureBtn.addEventListener('click', () => {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            const imageDataUrl = canvas.toDataURL('image/jpeg');
            photoPreview.src = imageDataUrl;
            photoPreview.style.display = 'block';
            video.style.display = 'none';
            captureBtn.style.display = 'none';
            startGradingBtn.style.display = 'block';
            document.querySelector('.close-camera-btn').remove();
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });

        fileInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    photoPreview.src = e.target.result;
                    photoPreview.style.display = 'block';
                    video.style.display = 'none';
                    captureBtn.style.display = 'none';
                    startGradingBtn.style.display = 'block';
                    scanOptions.style.display = 'none';
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        startGradingBtn.addEventListener('click', (event) => {
            event.preventDefault();
            const imageData = photoPreview.src.split(',')[1];
            const transcription = prompt('Enter a description or question about the image:');

            startGradingBtn.textContent = 'GRADING...';
            startGradingBtn.style.backgroundColor = 'gray';
            startGradingBtn.disabled = true;

            $.ajax({
                url: 'api/scan_api.php',
                method: 'POST',
                data: {
                    image: imageData,
                    transcription: transcription
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Raw API response:', response);
                    
                    if (response.status === 'success') {
                        let content = response.data.content;
                        
                        content = content.replace(/\\\(/g, '$');
                        content = content.replace(/\\\)/g, '$');
                        content = content.replace(/\\\[/g, '$$');
                        content = content.replace(/\\\]/g, '$$');
                        
                        content = content.replace(/#{1,4} (.*?)$/gm, '<b>$1</b>');
                        content = content.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
                        
                        content = content.replace(/\n/g, '<br>');
                        
                        result.innerHTML = '<h2>Result:</h2><p>' + content + '</p>' +
                            '<button id="copyBtn">Copy</button> ' +
                            '<button id="emailBtn">Email Feedback</button>';
                        
                        document.getElementById('copyBtn').addEventListener('click', copyResult);
                        document.getElementById('emailBtn').addEventListener('click', emailFeedback);
                        
                        function renderMath() {
                            if (window.MathJax && window.MathJax.typesetPromise) {
                                MathJax.typesetPromise([result]).then(() => {
                                    console.log('Math rendered successfully');
                                }).catch((err) => {
                                    console.error('Error rendering math:', err);
                                });
                            } else {
                                console.log('MathJax not ready, retrying in 100ms');
                                setTimeout(renderMath, 100);
                            }
                        }
                        
                        renderMath();
                    } else {
                        console.error('API Error:', response.message);
                        result.innerHTML = '<h2>Error:</h2><p>' + response.message + '</p>';
                    }

                    startGradingBtn.textContent = 'Launch Camera Again';
                    startGradingBtn.style.backgroundColor = 'green';
                    startGradingBtn.disabled = false;
                    startGradingBtn.onclick = resetCamera;
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    console.log('Response text:', xhr.responseText);
                    
                    result.innerHTML = '<h2>Error:</h2><p>An error occurred while processing the request.</p>';
                    
                    startGradingBtn.textContent = 'Start Grading';
                    startGradingBtn.style.backgroundColor = '';
                    startGradingBtn.disabled = false;
                }
            });
        });
    });

    function getPlainTextContent() {
        const resultElement = document.querySelector('#result p');
        let content = resultElement.innerHTML;

        const mathJaxElements = resultElement.querySelectorAll('.MathJax');
        mathJaxElements.forEach(element => {
            const original = element.getAttribute('data-mathml');
            if (original) {
                const parser = new DOMParser();
                const mathML = parser.parseFromString(original, 'text/xml');
                const annotation = mathML.querySelector('annotation[encoding="application/x-tex"]');
                if (annotation) {
                    let latex = annotation.textContent;
                    latex = latex.replace(/\\frac\{(\d+)\}\{(\d+)\}/g, '$1/$2');
                    latex = latex.replace(/\\left|\\\right/g, '');
                    content = content.replace(element.outerHTML, '$ ' + latex + ' $');
                }
            }
        });

        content = content.replace(/<br\s*\/?>/gi, '\n');
        content = content.replace(/<[^>]*>/g, '');

        const textarea = document.createElement('textarea');
        textarea.innerHTML = content;
        content = textarea.value;

        content = content.replace(/\s*\$\s*/g, ' $ ');

        content = content.replace(/([^\S\n]+)/g, ' ');

        content = content.replace(/(\d+)\/(\d+)/g, '$1 / $2');

        content = content.replace(/\s+([.,;:!?])/g, '$1');

        content = content.replace(/\n+/g, '\n\n');

        content = content.replace(/\$\s*-\\frac\{4\}\{3\}\s*\+\s*\$/g, '$ -4/3 + 1 = x $');

        return content.trim();
    }

    function copyResult(event) {
        event.preventDefault();
        const resultText = getPlainTextContent();
        navigator.clipboard.writeText(resultText).then(() => {
            const copyBtn = event.target;
            copyBtn.textContent = 'Copied!';
            setTimeout(() => {
                copyBtn.textContent = 'Copy';
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    }

    function emailFeedback(event) {
        event.preventDefault();
        const resultText = getPlainTextContent();
        const subject = encodeURIComponent('Feedback on your Assignment');
        const body = encodeURIComponent(resultText);
        window.location.href = `mailto:?subject=${subject}&body=${body}`;
    }

    function toggleMenu() {
        const menuOverlay = document.querySelector('.menu-overlay');
        const menuContent = document.querySelector('.menu-content');
        menuOverlay.style.display = menuOverlay.style.display === 'block' ? 'none' : 'block';
        menuContent.classList.toggle('active');
    }
    </script>
</body>
</html>