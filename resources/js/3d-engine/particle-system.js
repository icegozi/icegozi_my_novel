/**
 * ParticleSystem - Atmospheric Particle Cloud Generator for 3D Reader Projections
 * Supports: fireflies, snow, rain, divine_sparks, sakura, none
 */
export class ParticleSystem {
    constructor() {
        this.particleMesh = null;
        this.currentType = 'none';
        this.particleCount = 200;
        this.positions = null;
        this.velocities = null;
    }

    setEffect(type, scene) {
        this.destroy(scene);
        this.currentType = type || 'none';

        if (this.currentType === 'none' || !scene || typeof THREE === 'undefined') {
            return;
        }

        const geometry = new THREE.BufferGeometry();
        const positions = new Float32Array(this.particleCount * 3);
        const velocities = [];

        let color = 0xffffff;
        let size = 0.08;

        switch (this.currentType) {
            case 'fireflies':
                color = 0xa3e635; // Bright glowing green
                size = 0.12;
                break;
            case 'snow':
                color = 0xf8fafc; // Soft white
                size = 0.08;
                break;
            case 'rain':
                color = 0x38bdf8; // Blue streak
                size = 0.05;
                break;
            case 'divine_sparks':
                color = 0xfacc15; // Golden sparks
                size = 0.14;
                break;
            case 'sakura':
                color = 0xf472b6; // Cherry blossom pink
                size = 0.10;
                break;
        }

        for (let i = 0; i < this.particleCount; i++) {
            positions[i * 3] = (Math.random() - 0.5) * 6;
            positions[i * 3 + 1] = (Math.random() - 0.5) * 6;
            positions[i * 3 + 2] = (Math.random() - 0.5) * 6;

            velocities.push({
                x: (Math.random() - 0.5) * 0.01,
                y: this.currentType === 'rain' ? -0.06 : (this.currentType === 'divine_sparks' ? 0.04 : (Math.random() - 0.5) * 0.008),
                z: (Math.random() - 0.5) * 0.01
            });
        }

        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

        const material = new THREE.PointsMaterial({
            color: color,
            size: size,
            transparent: true,
            opacity: 0.8,
            blending: THREE.AdditiveBlending
        });

        this.particleMesh = new THREE.Points(geometry, material);
        this.positions = positions;
        this.velocities = velocities;

        scene.add(this.particleMesh);
    }

    update(delta = 0.016) {
        if (!this.particleMesh || !this.positions) return;

        const posAttr = this.particleMesh.geometry.attributes.position;
        const arr = posAttr.array;

        for (let i = 0; i < this.particleCount; i++) {
            const v = this.velocities[i];
            arr[i * 3] += v.x;
            arr[i * 3 + 1] += v.y;
            arr[i * 3 + 2] += v.z;

            // Reset bounds
            if (arr[i * 3 + 1] < -3) arr[i * 3 + 1] = 3;
            if (arr[i * 3 + 1] > 3) arr[i * 3 + 1] = -3;
            if (arr[i * 3] < -3) arr[i * 3] = 3;
            if (arr[i * 3] > 3) arr[i * 3] = -3;
        }

        posAttr.needsUpdate = true;
    }

    destroy(scene) {
        if (this.particleMesh) {
            if (scene) scene.remove(this.particleMesh);
            if (this.particleMesh.geometry) this.particleMesh.geometry.dispose();
            if (this.particleMesh.material) this.particleMesh.material.dispose();
            this.particleMesh = null;
        }
    }
}
