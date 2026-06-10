<?php // views/partials/lesson_modal.php 
?>
<div class="modal modal-xl fade" id="lessonModal" tabindex="-1" aria-labelledby="lessonModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="lessonModalLabel">Create Lesson</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/lesson/save" method="POST" id="lessonSaveForm">
                    <label for="lessonTitle" class="form-label">Lesson Title:</label>
                    <input type="text" name="lessonTitle" id="lessonTitle" class="form-control"><br>

                    <input type="hidden" name="lessonId" id="lessonId">
                    <input type="hidden" name="unitId" value="<?= htmlspecialchars($section['unit_id']) ?>">
                    <input type="hidden" name="sectionId" value="<?= htmlspecialchars($section['id']) ?>">

                    <label for="lessonBody" class="form-label">Lesson Body:</label>
                    <textarea id="lessonEditor" name="lessonBody" style="width:100%"></textarea>

                    <label for="lessonStatus" class="form-label">Status:</label>
                    <select class="form-select" name="lessonStatus" id="lessonStatus">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                    <?= csrf_input() ?>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="lessonSaveForm" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    let lessonEditor;
    window.addEventListener('DOMContentLoaded', () => {
        if (!lessonEditor) {
            lessonEditor = SUNEDITOR.create('lessonEditor', {
                height: '300px',
                buttonList: [
                    ['undo', 'redo'],
                    ['formatBlock'],
                    ['bold', 'underline', 'italic'],
                    ['list', 'align', 'horizontalRule'],
                    ['link', 'image', 'audio', 'table'],
                    ['codeView']
                ],
                audioFileInput: true,
                audioUrlInput: false,
                audioUploadUrl: '/audio/upload',
                tableDefaultWidth: '100%',
                tableDefaultStyle: 'border:1px solid #ccc;',
                tableHeaderEnabled: true
            });
        }
    });

    document.getElementById('lessonSaveForm')
        .addEventListener('submit', e => {
            document.getElementById('lessonEditor').value = lessonEditor.getContents(false);
        });

    const lessonModal = document.getElementById('lessonModal');
    lessonModal.addEventListener('show.bs.modal', event => {
        const trigger = event.relatedTarget;
        if (!trigger || !trigger.classList.contains('edit-btn')) {
            document.getElementById('lessonSaveForm').reset();
            document.getElementById('lessonId').value = '';
            document.getElementById('lessonModalLabel').textContent = 'Create Lesson';
            if (lessonEditor) {
                lessonEditor.setContents('');
            }
        }
    });
</script>