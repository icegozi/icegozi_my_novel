import { ParticleSystem } from './particle-system.js';
import { ModelCache } from './model-cache.js';
import { WebGLCapabilityDetector } from './webgl-fallback.js';

/**
 * ProjectionStage - Three.js WebGL Interactive 3D Hologram Projection Stage
 * Features:
 * - Touch Gesture Isolation: Canvas defaults to pointer-events: none on mobile
 * - Automatic BoundingBox centering and optimal camera distance
 * - DRACOLoader decompressed glTF parsing & ModelCache memory caching
 * - Dynamic atmospheric particle system
 * - WebGL context lost graceful recovery & 2D fallback for low-end hardware
 */
export class ProjectionStage {
    constructor(containerElement, options = {}) {
        this.container = containerElement;
        this.options = Object.assign({
            antialias: true,
            alpha: true,
            autoRotate: true,
            interactive: false,
            ambientLightColor: '#ffffff'
        }, options);

        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.controls = null;
        this.currentModel = null;
        this.animFrameId = null;

        this.isInteractive = false;
        this.isContextLost = false;
        this.particleSystem = new ParticleSystem();
        this.modelCache = new ModelCache();

        this.init();
    }

    init() {
        if (!this.container) return;

        // Check WebGL availability
        if (!WebGLCapabilityDetector.isWebGLAvailable() || typeof THREE === 'undefined') {
            console.warn('[ProjectionStage] WebGL is not available, falling back to 2D.');
            WebGLCapabilityDetector.render2dFallback(this.container, 'Đài Chiếu Cụ Hóa', null, 'Trình duyệt hoặc thiết bị không hỗ trợ WebGL.');
            return;
        }

        const width = this.container.clientWidth || 400;
        const height = this.container.clientHeight || 400;

        // 1. Scene
        this.scene = new THREE.Scene();

        // 2. Camera
        this.camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 100);
        this.camera.position.set(0, 1.2, 3.5);

        // 3. Renderer with powerPreference: 'high-performance'
        this.renderer = new THREE.WebGLRenderer({
            alpha: true,
            antialias: this.options.antialias,
            powerPreference: 'high-performance'
        });
        this.renderer.setSize(width, height);
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        this.renderer.outputColorSpace = THREE.SRGBColorSpace;

        this.container.appendChild(this.renderer.domElement);

        // Attach WebGL context lost detection: webglcontextlost handler
        WebGLCapabilityDetector.attachContextLostHandler(
            this.renderer.domElement,
            (e) => this.handleContextLost(e),
            (e) => this.handleContextRestored(e)
        );

        // 4. Touch isolation: default pointer-events none
        this.setInteractive(this.options.interactive);

        // 5. Lighting
        this.ambientLight = new THREE.AmbientLight(this.options.ambientLightColor, 1.2);
        this.scene.add(this.ambientLight);

        this.dirLight = new THREE.DirectionalLight(0xffffff, 1.5);
        this.dirLight.position.set(2, 4, 3);
        this.scene.add(this.dirLight);

        // 6. Orbit Controls
        if (typeof THREE.OrbitControls !== 'undefined') {
            this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
            this.controls.enableDamping = true;
            this.controls.dampingFactor = 0.05;
            this.controls.autoRotate = this.options.autoRotate;
            this.controls.autoRotateSpeed = 2.0;
            this.controls.enabled = this.isInteractive;
        }

        // 7. Handle resize
        this.resizeObserver = new ResizeObserver(() => this.handleResize());
        this.resizeObserver.observe(this.container);

        // 8. Start Render Loop
        this.animate = this.animate.bind(this);
        this.animate();
    }

    setInteractive(enabled) {
        this.isInteractive = !!enabled;
        if (this.renderer && this.renderer.domElement) {
            this.renderer.domElement.style.pointerEvents = this.isInteractive ? 'auto' : 'none';
        }
        if (this.controls) {
            this.controls.enabled = this.isInteractive;
        }
    }

    setLighting(color = '#ffffff') {
        if (this.ambientLight) {
            this.ambientLight.color.set(color);
        }
    }

    triggerEffect(particleType) {
        if (this.particleSystem && this.scene) {
            this.particleSystem.setEffect(particleType, this.scene);
        }
    }

    async loadModel(url, scaleFactor = 1.0, particleEffect = 'none') {
        if (!this.scene) return;

        // Clear existing model
        if (this.currentModel) {
            this.scene.remove(this.currentModel);
            this.currentModel = null;
        }

        // Apply particle atmosphere
        this.triggerEffect(particleEffect);

        if (!url) return;

        // Check cache
        if (this.modelCache.has(url)) {
            const cachedGltf = this.modelCache.get(url);
            this.attachModel(cachedGltf.clone(), scaleFactor);
            return;
        }

        // Load via GLTFLoader
        if (typeof THREE.GLTFLoader === 'undefined') {
            console.warn('[ProjectionStage] THREE.GLTFLoader not loaded.');
            return;
        }

        const loader = new THREE.GLTFLoader();

        // DRACO decompression setup if available
        if (typeof THREE.DRACOLoader !== 'undefined') {
            const dracoLoader = new THREE.DRACOLoader();
            dracoLoader.setDecoderPath('https://www.gstatic.com/draco/versioned/decoders/1.5.6/');
            loader.setDRACOLoader(dracoLoader);
        }

        return new Promise((resolve) => {
            loader.load(url, (gltf) => {
                this.modelCache.set(url, gltf.scene);
                this.attachModel(gltf.scene.clone(), scaleFactor);
                resolve(this.currentModel);
            }, undefined, (err) => {
                console.warn('[ProjectionStage] Could not load 3D file, generating fallback procedural artifact:', err);
                const fallbackGroup = this.createFallbackMesh();
                this.attachModel(fallbackGroup, scaleFactor);
                resolve(this.currentModel);
            });
        });
    }

    createFallbackMesh() {
        const group = new THREE.Group();
        const bladeGeo = new THREE.ConeGeometry(0.18, 1.8, 4);
        const bladeMat = new THREE.MeshStandardMaterial({
            color: 0x818cf8,
            emissive: 0x4f46e5,
            emissiveIntensity: 0.6,
            metalness: 0.8,
            roughness: 0.2
        });
        const blade = new THREE.Mesh(bladeGeo, bladeMat);
        blade.position.y = 0.9;
        group.add(blade);

        const guardGeo = new THREE.BoxGeometry(0.6, 0.08, 0.12);
        const guardMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b, metalness: 0.9, roughness: 0.1 });
        const guard = new THREE.Mesh(guardGeo, guardMat);
        group.add(guard);

        const hiltGeo = new THREE.CylinderGeometry(0.05, 0.05, 0.5, 8);
        const hiltMat = new THREE.MeshStandardMaterial({ color: 0x27272a, roughness: 0.8 });
        const hilt = new THREE.Mesh(hiltGeo, hiltMat);
        hilt.position.y = -0.28;
        group.add(hilt);

        return group;
    }

    attachModel(modelScene, scaleFactor = 1.0) {
        this.currentModel = modelScene;

        // Auto center geometry via BoundingBox
        const box = new THREE.Box3().setFromObject(this.currentModel);
        const center = box.getCenter(new THREE.Vector3());
        const size = box.getSize(new THREE.Vector3());

        this.currentModel.position.x += (this.currentModel.position.x - center.x);
        this.currentModel.position.y += (this.currentModel.position.y - center.y);
        this.currentModel.position.z += (this.currentModel.position.z - center.z);

        // Apply scale
        this.currentModel.scale.set(scaleFactor, scaleFactor, scaleFactor);

        // Adjust camera distance: (0, 0, maxDim * 2.5)
        const maxDim = Math.max(size.x, size.y, size.z);
        if (maxDim > 0 && this.camera) {
            const camDist = Math.max(maxDim * 2.5, 2.0);
            this.camera.position.set(0, 0, camDist);
            this.camera.lookAt(0, 0, 0);
        }

        this.scene.add(this.currentModel);
    }

    handleResize() {
        if (!this.container || !this.renderer || !this.camera) return;
        const width = this.container.clientWidth;
        const height = this.container.clientHeight;
        if (width === 0 || height === 0) return;

        this.camera.aspect = width / height;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(width, height);
    }

    animate() {
        this.animFrameId = requestAnimationFrame(this.animate);

        if (this.controls && this.controls.enabled) {
            this.controls.update();
        } else if (this.currentModel && this.options.autoRotate) {
            this.currentModel.rotation.y += 0.008;
        }

        if (this.particleSystem) {
            this.particleSystem.update();
        }

        if (this.renderer && this.scene && this.camera) {
            this.renderer.render(this.scene, this.camera);
        }
    }

    handleContextLost(event) {
        this.isContextLost = true;
        if (this.animFrameId) {
            cancelAnimationFrame(this.animFrameId);
            this.animFrameId = null;
        }
        const modelName = this.currentModel?.name || 'Mô Hình 3D';
        WebGLCapabilityDetector.render2dFallback(
            this.container,
            modelName,
            null,
            'Trình điều khiển đồ họa bị gián đoạn. Đã chuyển sang chế độ 2D an toàn.'
        );
    }

    handleContextRestored(event) {
        this.isContextLost = false;
        console.info('[ProjectionStage] Re-initializing WebGL scene after context restore...');
        this.init();
    }

    destroy() {
        if (this.animFrameId) {
            cancelAnimationFrame(this.animFrameId);
            this.animFrameId = null;
        }

        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
        }

        if (this.particleSystem) {
            this.particleSystem.destroy(this.scene);
        }

        if (this.controls) {
            this.controls.dispose();
        }

        if (this.renderer) {
            this.renderer.dispose();
            if (this.renderer.domElement && this.renderer.domElement.parentNode) {
                this.renderer.domElement.parentNode.removeChild(this.renderer.domElement);
            }
        }

        this.scene = null;
        this.camera = null;
        this.currentModel = null;
    }
}
