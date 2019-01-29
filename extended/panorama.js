// <script src="//cdnjs.cloudflare.com/ajax/libs/three.js/r69/three.min.js"></script>
// panorama_jpg('pnr','/1.jpg');

function panorama_jpg(id,jpg) { var eid=idd(id),X=eid.offsetWidth,Y=eid.offsetHeight;

	    var manualControl = false;
	    var longitude = 0;
	    var latitude = 0;
	    var savedX;
	    var savedY;
	    var savedLongitude;
	    var savedLatitude;
	    
	    // panoramas background
	    // var panoramasArray = ["/1.jpg"];
	    //  var panoramaNumber = Math.floor(Math.random()*panoramasArray.length);
 
	    // setting up the renderer
	    renderer = new THREE.WebGLRenderer();
	    // renderer.setSize(window.innerWidth, window.innerHeight);
	    renderer.setSize(X,Y);
	    eid.appendChild(renderer.domElement);
	    
	    // creating a new scene
	    var scene = new THREE.Scene();
	    
	    // adding a camera
	    var camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 1, 1000);
	    camera.target = new THREE.Vector3(0, 0, 0);
 
	    // creation of a big sphere geometry
	    var sphere = new THREE.SphereGeometry(100, 100, 40);
	    sphere.applyMatrix(new THREE.Matrix4().makeScale(-1, 1, 1));
 
	    // creation of the sphere material
	    var sphereMaterial = new THREE.MeshBasicMaterial();
	    sphereMaterial.map = THREE.ImageUtils.loadTexture(jpg);
 
	    // geometry + material = mesh (actual object)
	    var sphereMesh = new THREE.Mesh(sphere, sphereMaterial);
	    scene.add(sphereMesh);
 
	    // listeners
	    document.addEventListener("mousedown", onDocumentMouseDown, false);
	    document.addEventListener("mousemove", onDocumentMouseMove, false);
	    document.addEventListener("mouseup", onDocumentMouseUp, false);
		
               
            function render(){
			requestAnimationFrame(render);
			if(!manualControl){ longitude += 0.1; }
 
		// limiting latitude from -85 to 85 (cannot point to the sky or under your feet)
                    latitude = Math.max(-85, Math.min(85, latitude));
 
		// moving the camera according to current latitude (vertical movement) and longitude (horizontal movement)
		camera.target.x = 500 * Math.sin(THREE.Math.degToRad(90 - latitude)) * Math.cos(THREE.Math.degToRad(longitude));
		camera.target.y = 500 * Math.cos(THREE.Math.degToRad(90 - latitude));
		camera.target.z = 500 * Math.sin(THREE.Math.degToRad(90 - latitude)) * Math.sin(THREE.Math.degToRad(longitude));
		camera.lookAt(camera.target);
 
		// calling again render function
		renderer.render(scene, camera);
	    }
	    
	    // when the mouse is pressed, we switch to manual control and save current coordinates
	    function onDocumentMouseDown(event){
 		event.preventDefault();
 		manualControl = true;
 		savedX = event.clientX;
		savedY = event.clientY;
 		savedLongitude = longitude;
		savedLatitude = latitude;
 	    }
 
	    // when the mouse moves, if in manual contro we adjust coordinates
	    function onDocumentMouseMove(event){
 		if(manualControl){
		    longitude = (savedX - event.clientX) * 0.1 + savedLongitude;
		    latitude = (event.clientY - savedY) * 0.1 + savedLatitude;
		}
 	    }
 
	    // when the mouse is released, we turn manual control off
	    function onDocumentMouseUp(event){
 		manualControl = false;
 	    }
	    
	    // pressing a key (actually releasing it) changes the texture map
	    // document.onkeyup = function(event){
		// panoramaNumber = (panoramaNumber + 1) % panoramasArray.length
	// 	sphereMaterial.map = THREE.ImageUtils.loadTexture(panoramasArray[panoramaNumber])
	//     }

           render();
}
