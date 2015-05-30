<!DOCTYPE html>
<html lang="en">
	<head>
		<title>teste movimento</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
		<style>
			body {
				font-family: Monospace;
				background-color: #f0f0f0;
				margin: 0px;
				overflow: hidden;
			}
		</style>
	</head>
	<body>

		<?php
			$arquivo = fopen('pos.txt','r+');
			if ($arquivo == false) die('O arquivo não existe.');
			$linhas = array();
			$i = 0;
			while(true) {
				$linhas[$i] = fgets($arquivo);
				if ($linhas[$i]==null) break;
				$i = $i + 1;
			}
			fclose($arquivo);
		?>

		<script src="js/three.min.js"></script>
		<script src="js/TrackballControls.js"></script>
		<script src="js/JSONLoader.js">> </script>


		<script>
			var container;
			var camera, controls, scene, renderer;
			var objects = [];
			var plane;

			var raycaster = new THREE.Raycaster();
			var mouse = new THREE.Vector2(),
			offset = new THREE.Vector3(),
			INTERSECTED, SELECTED;

			var rx,rz;
			init();
			animate();

			function init() {

				container = document.createElement( 'div' );
				document.body.appendChild( container );

				camera = new THREE.PerspectiveCamera( 70, window.innerWidth / window.innerHeight, 1, 10000 );
				camera.position.z = 1000;
				camera.position.y = 100;
				camera.rotation.x = 180 * Math.PI / 180;

				controls = new THREE.TrackballControls( camera );
				controls.rotateSpeed = 1.0;
				controls.zoomSpeed = 1.2;
				controls.panSpeed = 0.8;

				scene = new THREE.Scene();

				scene.add( new THREE.AmbientLight( 0x505050 ) );

				var light = new THREE.DirectionalLight( 0xffffff, 1 );
				light.position.set( 0, 500, 0 ).normalize();
				light.rotation.x = 180 * Math.PI / 180;

				scene.add( light );

				var geometry = new THREE.BoxGeometry( 7059, 3246, 1 );
				var textura = THREE.ImageUtils.loadTexture('img/mapacompleto.jpg');
				var material = new THREE.MeshLambertMaterial( { map: textura } );
				plano = new THREE.Mesh(geometry,material);
				plano.rotation.x = 90 * Math.PI / 180;
				scene.add(plano);

				var loader = new THREE.JSONLoader();
				loader.load( "mods/museunatural_ch_yan.js", function( geometry ) {
					var material = new THREE.MeshPhongMaterial( { color:0xacdfe3 } );
					var mesh = new THREE.Mesh( geometry, material );
					var ax = <?php echo $linhas[2]; ?>;
					var az = <?php echo $linhas[3]; ?>;
					alert(ax);
					mesh.position.set( ax , 2 , az );
					mesh.scale.set(10, 10, 10);

					scene.add( mesh );
					objects.push( mesh );
				});

				plane = new THREE.Mesh(
					new THREE.PlaneBufferGeometry( 2000, 2000, 8, 8 ),
					new THREE.MeshBasicMaterial( { color: 0x000000, opacity: 0.25, transparent: true } )
				);
				plane.visible = false;
				scene.add( plane );

				renderer = new THREE.WebGLRenderer( { antialias: true } );
				renderer.setClearColor( 0x000000 );
				renderer.setPixelRatio( window.devicePixelRatio );
				renderer.setSize( window.innerWidth, window.innerHeight );
				renderer.sortObjects = false;

				renderer.shadowMapEnabled = true;
				renderer.shadowMapType = THREE.PCFShadowMap;

				container.appendChild( renderer.domElement );

				renderer.domElement.addEventListener( 'mousemove', onDocumentMouseMove, false );
				renderer.domElement.addEventListener( 'mousedown', onDocumentMouseDown, false );
				renderer.domElement.addEventListener( 'mouseup', onDocumentMouseUp, false );

				//

				window.addEventListener( 'resize', onWindowResize, false );
				window.addEventListener('onbeforeunload', onbeforeunload, false);
			}

			function onbeforeunload(event) {
				var message = 'Important: Please click on \'Save\' button to leave this page.';
			    if (typeof event == 'undefined') {
			        event = window.event;
			    }
			    if (event) {
			        event.returnValue = message;
			        rx = mesh.pos.x;
			        rz = mesh.pos.z;
			    }
			    return message;
			};

			function onWindowResize() {

				camera.aspect = window.innerWidth / window.innerHeight;
				camera.updateProjectionMatrix();

				renderer.setSize( window.innerWidth, window.innerHeight );

			}

			function onDocumentMouseMove( event ) {

				event.preventDefault();

				mouse.x = ( event.clientX / window.innerWidth ) * 2 - 1;
				mouse.y = - ( event.clientY / window.innerHeight ) * 2 + 1;

				//teste2

				raycaster.setFromCamera( mouse, camera );

				if ( SELECTED ) {

					var intersects = raycaster.intersectObject( plane );
					//SELECTED.position.copy( intersects[ 0 ].point.sub( offset ) );
					SELECTED.position.z = intersects [ 0 ].point.z;
					return;

				}

				var intersects = raycaster.intersectObjects( objects );

				if ( intersects.length > 0 ) {

					if ( INTERSECTED != intersects[ 0 ].object ) {

						if ( INTERSECTED ) INTERSECTED.material.color.setHex( INTERSECTED.currentHex );

						INTERSECTED = intersects[ 0 ].object;
						INTERSECTED.currentHex = INTERSECTED.material.color.getHex();

						plane.position.copy( INTERSECTED.position );
						plane.lookAt( camera.position );

					}

					container.style.cursor = 'pointer';

				} else {

					if ( INTERSECTED ) INTERSECTED.material.color.setHex( INTERSECTED.currentHex );

					INTERSECTED = null;

					container.style.cursor = 'auto';

				}

			}

			function onDocumentMouseDown( event ) {

				event.preventDefault();

				var vector = new THREE.Vector3( mouse.x, mouse.y, 0.5 ).unproject( camera );

				var raycaster = new THREE.Raycaster( camera.position, vector.sub( camera.position ).normalize() );

				var intersects = raycaster.intersectObjects( objects );

				if ( intersects.length > 0 ) {

					controls.enabled = false;

					SELECTED = intersects[ 0 ].object;

					var intersects = raycaster.intersectObject( plane );
					offset.copy( intersects[ 0 ].point ).sub( plane.position );
					container.style.cursor = 'move';

				}

			}

			function onDocumentMouseUp( event ) {

				event.preventDefault();

				controls.enabled = true;

				if ( INTERSECTED ) {

					plane.position.copy( INTERSECTED.position.x );

					SELECTED = null;

				}

				container.style.cursor = 'auto';

			}

			//

			function animate() {

				requestAnimationFrame( animate );

				render();

			}

			function render() {

				controls.update();

				renderer.render( scene, camera );

			}

		</script>
		<?php
			$arquivo = fopen('pos.txt','w+');
		    if ($arquivo == false) die('O arquivo não existe.');
		    $nome = "museu";
		    $nome .= "\n";
			fwrite($arquivo, $nome);
			$posx = "<script>document.write(rx)</script>";
			$posx .= "\n";
			$posz = "<script>document.write(rz)</script>";
			$posz .= "\n";
			fwrite($arquivo, 10);
			fwrite($arquivo, 10);
			fclose($arquivo);
		?>

	</body>
</html>
