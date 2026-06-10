<?php // views/partials/unit_modal.php 
?>
<div class="modal modal-xl fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Create Unit</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/unit/save" method="POST" id="saveForm">
                    <label for="unitTitle" class="form-label">Unit Title:</label>
                    <input type="text" name="unitTitle" id="unitTitle" class="form-control"><br>
                    <input type="hidden" name="unitId" id="unitId">
                    <label for="unitBody" class="form-label">Unit Body:</label>
                    <!-- this textarea will get overwritten with the editor HTML -->
                    <textarea id="sample" name="unitBody" style="width:100%"></textarea>
                    <label for="unitStatus" class="form-label">Status:</label>
                    <select class="form-select" name="unitStatus" id="unitStatus">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                    <?= csrf_input() ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
                <!-- this button submits the form -->
                <button type="submit" form="saveForm" class="btn btn-primary">
                    Save changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let lessonEditor;
    window.addEventListener('DOMContentLoaded', () => {
        if (!lessonEditor) {
            lessonEditor = SUNEDITOR.create('sample', {
                height: '300px',
                buttonList: [
                    ['undo', 'redo'],
                    ['formatBlock'],
                    ['bold', 'underline', 'italic'],
                    ['list', 'align', 'horizontalRule'],
                    ['link', 'image', 'audio', 'table'],
                    ['codeView']
                ],
                // === audio upload config ===
                audioFileInput: true, // show a file-picker in the audio dialog
                audioUrlInput: false, // hide the URL‐input field
                audioUploadUrl: '/audio/upload', // endpoint that handles the upload
                audioUploadHeader: {}, // any custom headers, if needed
                // audioMultipleFile: false,      // default = false
                // audioUploadSizeLimit: 10485760, // e.g. 10MB limit
                // optional: default table settings
                tableDefaultWidth: '100%', // when you insert a table, width="100%"
                tableDefaultStyle: 'border:1px solid #ccc;',
                tableMaxWidth: null, // no max width
                tableHeaderEnabled: true // allow header row checkbox
            });

        }
    });

    document.getElementById('saveForm')
        .addEventListener('submit', e => {
            // getContents(false) returns the full HTML
            document.getElementById('sample').value = lessonEditor.getContents(false);
            // now the POST will include the HTML in `unitBody`
        });

    const unitModal = document.getElementById('exampleModal');
    unitModal.addEventListener('show.bs.modal', event => {
        const trigger = event.relatedTarget;
        if (!trigger || !trigger.classList.contains('edit-btn')) {
            document.getElementById('saveForm').reset();
            document.getElementById('unitId').value = '';
            document.getElementById('exampleModalLabel').textContent = 'Create Unit';
            if (lessonEditor) {
                lessonEditor.setContents('');
            }
        }
    });
</script>