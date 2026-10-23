/**
 * Client-side 3D Asset Uploader Helper
 * Checks client-side binary glTF header and file size before upload
 */
export class Asset3dUploader {
    constructor(fileInputId, previewContainerId) {
        this.fileInput = document.getElementById(fileInputId);
        this.previewContainer = document.getElementById(previewContainerId);
        this.init();
    }

    init() {
        if (!this.fileInput) return;

        this.fileInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            // 1. Check file size (max 50MB)
            if (file.size > 50 * 1024 * 1024) {
                alert('Tệp vượt quá giới hạn 50MB cho phép!');
                this.fileInput.value = '';
                return;
            }

            // 2. Validate magic bytes glTF in client memory
            const slice = file.slice(0, 4);
            const buffer = await slice.arrayBuffer();
            const view = new DataView(buffer);
            const magic = String.fromCharCode(
                view.getUint8(0),
                view.getUint8(1),
                view.getUint8(2),
                view.getUint8(3)
            );

            if (magic !== 'glTF') {
                alert('Tệp không đúng định dạng 3D GLB nhị phân (thiếu header glTF)!');
                this.fileInput.value = '';
                return;
            }

            console.log(`[3D Uploader] Validated GLB model: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`);
        });
    }
}
