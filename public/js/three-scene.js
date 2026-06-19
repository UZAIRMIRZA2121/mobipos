// MobiPOS Three.js Scene Setup

document.addEventListener("DOMContentLoaded", () => {
    // 1. HERO SCENE
    const heroCanvas = document.getElementById('hero-canvas');
    if (heroCanvas) {
        initHeroScene(heroCanvas);
    }

    // 2. SHOWCASE SCENE
    const showcaseCanvas = document.getElementById('showcase-canvas');
    if (showcaseCanvas) {
        initShowcaseScene(showcaseCanvas);
    }
});

function createRoundedRectShape(width, height, radius) {
    const shape = new THREE.Shape();
    const x = -width / 2;
    const y = -height / 2;
    
    shape.moveTo(x, y + radius);
    shape.lineTo(x, y + height - radius);
    shape.quadraticCurveTo(x, y + height, x + radius, y + height);
    shape.lineTo(x + width - radius, y + height);
    shape.quadraticCurveTo(x + width, y + height, x + width, y + height - radius);
    shape.lineTo(x + width, y + radius);
    shape.quadraticCurveTo(x + width, y, x + width - radius, y);
    shape.lineTo(x + radius, y);
    shape.quadraticCurveTo(x, y, x, y + radius);
    return shape;
}

function createDeviceMesh(type) {
    const group = new THREE.Group();
    let width, height, depth, color;
    
    switch(type) {
        case 'phone':
            width = 1.2; height = 2.4; depth = 0.15; color = 0x00E5FF;
            break;
        case 'tablet':
            width = 2.5; height = 3.5; depth = 0.15; color = 0x4F46E5;
            break;
        case 'laptop':
            width = 4.0; height = 2.5; depth = 0.2; color = 0x00FF95;
            break;
        default:
            width = 1; height = 2; depth = 0.1; color = 0xffffff;
    }

    const shape = createRoundedRectShape(width, height, 0.2);
    const extrudeSettings = { depth: depth, bevelEnabled: true, bevelSegments: 3, steps: 2, bevelSize: 0.05, bevelThickness: 0.05 };
    const geometry = new THREE.ExtrudeGeometry(shape, extrudeSettings);
    
    const material = new THREE.MeshPhysicalMaterial({
        color: color,
        metalness: 0.5,
        roughness: 0.1,
        transparent: true,
        opacity: 0.8,
        clearcoat: 1.0,
        clearcoatRoughness: 0.1
    });

    const mesh = new THREE.Mesh(geometry, material);
    mesh.position.z = -depth/2; // Center it
    group.add(mesh);
    
    // Attempt to load realistic GLTF model for phones
    if (type === 'phone' && window.THREE.GLTFLoader) {
        const loader = new THREE.GLTFLoader();
        // Using a public CDN iPhone model
        loader.load('https://vazxmixyzorpmqcqqu2w.supabase.co/storage/v1/object/public/models/iphone-x/model.gltf', function(gltf) {
            // Success! Hide the fallback geometry and show the real model
            mesh.visible = false;
            const model = gltf.scene;
            
            // Adjust scale and position to match our scene
            model.scale.set(0.8, 0.8, 0.8);
            
            // Re-center model
            const box = new THREE.Box3().setFromObject(model);
            const center = box.getCenter(new THREE.Vector3());
            model.position.sub(center);
            
            group.add(model);
        }, undefined, function(error) {
            console.log("Could not load realistic phone model, using fallback geometry.");
        });
    }

    return group;
}

function initHeroScene(container) {
    const scene = new THREE.Scene();
    
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.z = 10;

    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // Lights
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    scene.add(ambientLight);

    const pointLight1 = new THREE.PointLight(0x00E5FF, 2, 50);
    pointLight1.position.set(5, 5, 5);
    scene.add(pointLight1);

    const pointLight2 = new THREE.PointLight(0x00FF95, 2, 50);
    pointLight2.position.set(-5, -5, 5);
    scene.add(pointLight2);
    
    // Add directional light for realistic model reflections
    const dirLight = new THREE.DirectionalLight(0xffffff, 2);
    dirLight.position.set(2, 5, 5);
    scene.add(dirLight);

    // Group for objects
    const group = new THREE.Group();
    scene.add(group);

    // Main Phone
    const mainPhone = createDeviceMesh('phone');
    mainPhone.scale.set(1.5, 1.5, 1.5);
    group.add(mainPhone);

    // Floating devices
    const devices = [];
    const types = ['phone', 'tablet', 'phone', 'laptop', 'phone', 'tablet'];
    
    for(let i=0; i<6; i++) {
        const mesh = createDeviceMesh(types[i]);
        
        // Random positions in a sphere around main phone
        const theta = Math.random() * Math.PI * 2;
        const phi = Math.acos(Math.random() * 2 - 1);
        const radius = 4 + Math.random() * 3;
        
        mesh.position.x = radius * Math.sin(phi) * Math.cos(theta);
        mesh.position.y = radius * Math.sin(phi) * Math.sin(theta);
        mesh.position.z = radius * Math.cos(phi) - 2;

        mesh.rotation.x = Math.random() * Math.PI;
        mesh.rotation.y = Math.random() * Math.PI;

        devices.push({
            mesh: mesh,
            rx: (Math.random() - 0.5) * 0.02,
            ry: (Math.random() - 0.5) * 0.02
        });
        group.add(mesh);
    }

    // Particles
    const particlesGeometry = new THREE.BufferGeometry();
    const particlesCount = 500;
    const posArray = new Float32Array(particlesCount * 3);
    for(let i = 0; i < particlesCount * 3; i++) {
        posArray[i] = (Math.random() - 0.5) * 20;
    }
    particlesGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
    const particlesMaterial = new THREE.PointsMaterial({
        size: 0.05,
        color: 0x00E5FF,
        transparent: true,
        opacity: 0.5
    });
    const particlesMesh = new THREE.Points(particlesGeometry, particlesMaterial);
    scene.add(particlesMesh);

    // Mouse movement
    let mouseX = 0;
    let mouseY = 0;
    let targetX = 0;
    let targetY = 0;

    const windowHalfX = window.innerWidth / 2;
    const windowHalfY = window.innerHeight / 2;

    document.addEventListener('mousemove', (event) => {
        mouseX = (event.clientX - windowHalfX);
        mouseY = (event.clientY - windowHalfY);
    });

    // Animation Loop
    function animate() {
        requestAnimationFrame(animate);

        targetX = mouseX * 0.001;
        targetY = mouseY * 0.001;

        group.rotation.y += 0.005;
        group.rotation.x += 0.002;
        
        mainPhone.rotation.y += 0.01;

        // Smoothly move group based on mouse
        group.rotation.y += 0.05 * (targetX - group.rotation.y);
        group.rotation.x += 0.05 * (targetY - group.rotation.x);

        devices.forEach(d => {
            d.mesh.rotation.x += d.rx;
            d.mesh.rotation.y += d.ry;
        });

        particlesMesh.rotation.y -= 0.001;

        renderer.render(scene, camera);
    }

    animate();

    // Resize
    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });
}

function initShowcaseScene(container) {
    const scene = new THREE.Scene();
    
    const width = container.clientWidth;
    const height = container.clientHeight;

    const camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 1000);
    camera.position.set(0, 3, 8);
    camera.lookAt(0, 0, 0);

    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
    scene.add(ambientLight);

    const spotLight = new THREE.SpotLight(0x00E5FF, 2);
    spotLight.position.set(5, 10, 5);
    scene.add(spotLight);

    const spotLight2 = new THREE.SpotLight(0x4F46E5, 2);
    spotLight2.position.set(-5, 10, -5);
    scene.add(spotLight2);
    
    // Front light for realistic models
    const frontLight = new THREE.DirectionalLight(0xffffff, 1.5);
    frontLight.position.set(0, 2, 5);
    scene.add(frontLight);

    const group = new THREE.Group();
    scene.add(group);

    // Floor
    const floorGeo = new THREE.PlaneGeometry(15, 15);
    const floorMat = new THREE.MeshStandardMaterial({ 
        color: 0x0a0a1a, 
        roughness: 0.1, 
        metalness: 0.8,
        transparent: true,
        opacity: 0.5
    });
    const floor = new THREE.Mesh(floorGeo, floorMat);
    floor.rotation.x = -Math.PI / 2;
    floor.position.y = -2;
    group.add(floor);

    // Abstract Shop Counter
    const counterGeo = new THREE.BoxGeometry(6, 1.5, 2);
    const counterMat = new THREE.MeshStandardMaterial({ color: 0x111122, metalness: 0.5, roughness: 0.2 });
    const counter = new THREE.Mesh(counterGeo, counterMat);
    counter.position.set(0, -1.25, 2);
    group.add(counter);

    // Abstract Shelves
    const shelfGeo = new THREE.BoxGeometry(8, 0.2, 1);
    const shelfMat = new THREE.MeshStandardMaterial({ color: 0x222244, metalness: 0.3 });
    
    for(let i=0; i<3; i++) {
        const shelf = new THREE.Mesh(shelfGeo, shelfMat);
        shelf.position.set(0, -0.5 + i*1.5, -2);
        group.add(shelf);
        
        // Add some random phones on shelves
        for(let j=0; j<4; j++) {
            const phone = createDeviceMesh('phone');
            phone.scale.set(0.4, 0.4, 0.4);
            phone.position.set(-3 + j*2, -0.5 + i*1.5 + 0.6, -1.8);
            group.add(phone);
        }
    }

    function animate() {
        requestAnimationFrame(animate);
        group.rotation.y += 0.003;
        renderer.render(scene, camera);
    }
    animate();

    window.addEventListener('resize', () => {
        const newW = container.clientWidth;
        const newH = container.clientHeight;
        camera.aspect = newW / newH;
        camera.updateProjectionMatrix();
        renderer.setSize(newW, newH);
    });
}
