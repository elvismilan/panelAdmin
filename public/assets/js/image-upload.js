/**
 * image-upload.js
 * Drag-and-drop image preview component logic.
 * No dependencies — plain vanilla JS.
 */
function imgUploadInit(zoneId, inputId, previewId, infoId, clearId) {
    var zone     = document.getElementById(zoneId);
    var input    = document.getElementById(inputId);
    var preview  = document.getElementById(previewId);
    var pholder  = document.getElementById(previewId + '_placeholder');
    var img      = document.getElementById(previewId + '_img');
    var info     = document.getElementById(infoId);
    var iname    = document.getElementById(infoId + '_name');
    var isize    = document.getElementById(infoId + '_size');
    var clearBtn = document.getElementById(clearId);

    if (!zone || !input) return;

    function formatBytes(bytes) {
        if (bytes < 1024)    return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    function showPreview(file) {
        var reader = new FileReader();
        reader.onload = function (e) {
            img.src               = e.target.result;
            preview.style.display = '';
            pholder.style.display = 'none';
            info.style.display    = '';
            iname.textContent     = file.name;
            isize.textContent     = '(' + formatBytes(file.size) + ')';
            clearBtn.style.display = '';
        };
        reader.readAsDataURL(file);
    }

    zone.addEventListener('click', function () {
        input.click();
    });

    input.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    input.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            showPreview(this.files[0]);
        }
    });

    zone.addEventListener('dragover', function (e) {
        e.preventDefault();
        zone.style.borderColor = '#7366ff';
        zone.style.background  = '#f0eeff';
    });

    zone.addEventListener('dragleave', function () {
        zone.style.borderColor = '#ced4da';
        zone.style.background  = '#fafafa';
    });

    zone.addEventListener('drop', function (e) {
        e.preventDefault();
        zone.style.borderColor = '#ced4da';
        zone.style.background  = '#fafafa';
        var files = e.dataTransfer.files;
        if (files && files[0]) {
            var dt = new DataTransfer();
            dt.items.add(files[0]);
            input.files = dt.files;
            showPreview(files[0]);
        }
    });
}

function imgUploadClear(zoneId, inputId, previewId, infoId, clearId) {
    var input    = document.getElementById(inputId);
    var preview  = document.getElementById(previewId);
    var pholder  = document.getElementById(previewId + '_placeholder');
    var img      = document.getElementById(previewId + '_img');
    var info     = document.getElementById(infoId);
    var clearBtn = document.getElementById(clearId);

    if (!input) return;

    input.value            = '';
    img.src                = '';
    preview.style.display  = 'none';
    pholder.style.display  = '';
    info.style.display     = 'none';
    clearBtn.style.display = 'none';
}
