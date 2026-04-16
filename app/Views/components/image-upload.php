<?php
/**
 * Drag-and-drop image upload component.
 * Requires: /assets/js/image-upload.js (included once per page in the view that uses this component)
 *
 * Variables to set before including:
 *   $uploadFieldName  string  Name attribute of the file input.           Default: 'foto'
 *   $uploadLabel      string  Human-readable label text.                   Default: 'Foto'
 *   $uploadCurrent    string  Relative path of current image (edit mode).  Default: ''
 *   $uploadNote       string  Helper note shown below the drop zone.        Default: 'JPG, PNG, GIF, WEBP — max. 2 MB'
 *   $uploadId         string  Unique ID suffix (avoid conflicts per page).  Default: '1'
 */
$uploadFieldName = $uploadFieldName ?? 'foto';
$uploadLabel     = $uploadLabel     ?? 'Foto';
$uploadCurrent   = $uploadCurrent   ?? '';
$uploadNote      = $uploadNote      ?? 'JPG, PNG, GIF, WEBP — max. 2 MB';
$uploadId        = $uploadId        ?? '1';

$zoneId      = 'imgZone_'    . $uploadId;
$inputId     = 'imgInput_'   . $uploadId;
$previewId   = 'imgPreview_' . $uploadId;
$infoId      = 'imgInfo_'    . $uploadId;
$clearId     = 'imgClear_'   . $uploadId;
$safeField   = htmlspecialchars($uploadFieldName, ENT_QUOTES, 'UTF-8');
$safeCurrent = htmlspecialchars($uploadCurrent,   ENT_QUOTES, 'UTF-8');
$hasCurrent  = $uploadCurrent !== '';
?>
<div class="mb-3">
    <label class="form-label"><?= htmlspecialchars($uploadLabel, ENT_QUOTES, 'UTF-8') ?></label>

    <!-- Drop zone + controls side by side -->
    <div style="display:flex;align-items:flex-start;gap:14px;">

        <!-- Drop zone (avatar square) -->
        <div id="<?= $zoneId ?>"
             style="flex-shrink:0;display:flex;flex-direction:column;align-items:center;justify-content:center;
                    width:130px;height:130px;border:2px dashed #ced4da;border-radius:10px;
                    cursor:pointer;background:#fafafa;transition:border-color .2s,background .2s;
                    overflow:hidden;">

            <!-- Placeholder -->
            <div id="<?= $previewId ?>_placeholder"
                 style="display:<?= $hasCurrent ? 'none' : 'flex' ?>;flex-direction:column;align-items:center;
                        justify-content:center;gap:6px;padding:10px;text-align:center;">
                <i class="fa fa-user-circle-o fa-3x text-muted" aria-hidden="true"></i>
                <span style="font-size:.72rem;color:#6c757d;line-height:1.3;">Arrastra o clic</span>
            </div>

            <!-- Preview -->
            <div id="<?= $previewId ?>" style="display:<?= $hasCurrent ? 'block' : 'none' ?>;width:100%;height:100%;">
                <img id="<?= $previewId ?>_img"
                     src="<?= $hasCurrent ? '/' . $safeCurrent : '' ?>"
                     alt="Vista previa"
                     style="width:100%;height:100%;object-fit:cover;">
            </div>

            <!-- Hidden input -->
            <input id="<?= $inputId ?>"
                   type="file"
                   name="<?= $safeField ?>"
                   accept="image/jpeg,image/png,image/gif,image/webp"
                   style="display:none;">
        </div>

        <!-- Right side: buttons + info + note -->
        <div style="display:flex;flex-direction:column;justify-content:center;gap:6px;padding-top:4px;">
            <button type="button"
                    class="btn btn-sm btn-outline-primary"
                    onclick="document.getElementById('<?= $inputId ?>').click()">
                <i class="fa fa-folder-open-o me-1" aria-hidden="true"></i> Seleccionar
            </button>
            <button type="button"
                    id="<?= $clearId ?>"
                    class="btn btn-sm btn-outline-secondary"
                    style="display:<?= $hasCurrent ? 'block' : 'none' ?>;"
                    onclick="imgUploadClear('<?= $zoneId ?>','<?= $inputId ?>','<?= $previewId ?>','<?= $infoId ?>','<?= $clearId ?>')">
                <i class="fa fa-times me-1" aria-hidden="true"></i> Quitar
            </button>
            <div id="<?= $infoId ?>"
                 style="display:<?= $hasCurrent ? 'block' : 'none' ?>;font-size:.72rem;color:#6c757d;
                        max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                <span id="<?= $infoId ?>_name"><?= $hasCurrent ? basename($safeCurrent) : '' ?></span>
                <span id="<?= $infoId ?>_size"></span>
            </div>
            <small class="text-muted"><?= htmlspecialchars($uploadNote, ENT_QUOTES, 'UTF-8') ?></small>
        </div>

    </div>

    <!-- Init call (deferred after DOM + image-upload.js are loaded) -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        imgUploadInit(
            '<?= $zoneId ?>',
            '<?= $inputId ?>',
            '<?= $previewId ?>',
            '<?= $infoId ?>',
            '<?= $clearId ?>'
        );
    });
    </script>
</div>
