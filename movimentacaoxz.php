<!DOCTYPE html>
<!-- -->
<html lang="pt">
	<head>
		<title>Mudar Título</title>
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
		<!--
		<?php
			//Abre um arquivo de nome 'pos.txt' para a leitura e escrita no inicio do arquivo;
			/*
			$arquivo = fopen('pos.txt','r+');
			if ($arquivo == false) die('O arquivo não existe.');
			$linhas = array();
			$i = 0;

			//Le cada linha do arquivo, guardando seu conteudo em $linhas[$i];
			while(true) {
				$linhas[$i] = fgets($arquivo);
				if ($linhas[$i]==null) break;
				$i = $i + 1;
			}

			//Fecha o arquivo.
			fclose($arquivo);*/
		?>
		-->
		<!-- Scripts utilizados pelo código. -->
		<script src="js/three.min.js"></script>
		<script src="js/TrackballControls.js"></script>
		<script src="js/JSONLoader.js">> </script>

		<script>
			//Criação das variaveis globais.
			var container;
			var camera, controls, scene, renderer;
			var objects = [];
			var plane;

			var raycaster = new THREE.Raycaster();
			var mouse = new THREE.Vector2(),
			offset = new THREE.Vector3(),
			INTERSECTED, SELECTED;

			var rx,rz;
			var mesh;

			//Inicialização das Funções de Animação
			init();
			animate();


			function init() {
				//Função responsavel por inicializar e preparar a cena.

				//Cria uma div na página.
				container = document.createElement( 'div' );
				document.body.appendChild( container );

				//Cria uma camera "Perspectiva" e "seta" sua posição.
				camera = new THREE.PerspectiveCamera( 70, window.innerWidth / window.innerHeight, 1, 10000 );
				camera.position.set(3091.40 , 100 , -863.60);

				//Da as propriedades de um TrackballControls para a camera.
				controls = new THREE.TrackballControls( camera );
				controls.rotateSpeed = 0.25;
				controls.zoomSpeed = 0.3;
				controls.panSpeed = 0.3;

				//cria uma cena e adiciona iluminação.
				scene = new THREE.Scene();

				scene.add( new THREE.AmbientLight( 0x505050 ) );

				var light = new THREE.DirectionalLight( 0xffffff, 1 );
				light.position.set( 0, 500, 0 ).normalize();
				light.rotation.x = 180 * Math.PI / 180;

				scene.add( light );

				//Cria o plano, adiciona uma textura e o posiciona.
				var geometry = new THREE.BoxGeometry( 7059, 3246, 1 );
				var textura = THREE.ImageUtils.loadTexture('img/mapacompleto.jpg');
				var material = new THREE.MeshLambertMaterial( { map: textura } );
				plano = new THREE.Mesh(geometry,material);
				plano.rotation.x = 90 * Math.PI / 180;
				scene.add(plano);

				//Carrega um modelo utilizando o JSONLoader, pega as posições X e Z de um arquivo, posiciona o objeto, escala o objeto e o adiciona na cena.
				var loader = new THREE.JSONLoader();
				loader.load( "mods/08.museu.js", function( geometry ) {
					var material = new THREE.MeshPhongMaterial( { color:0xacdfe3 } );
					mesh = new THREE.Mesh( geometry, material );
					mesh.position.set( 3136.63 , 9 , -1054.72 );
					mesh.rotation.y = -46 * Math.PI / 180;
					mesh.scale.set(6, 8, 5);
					scene.add( mesh );
					objects.push( mesh );
				});


				//Cria o plano de fundo na cor preta e adiciona a cena.
				plane = new THREE.Mesh(
					new THREE.PlaneBufferGeometry( 2000, 2000, 8, 8 ),
					new THREE.MeshBasicMaterial( { color: 0x000000, opacity: 0.25, transparent: true } )
				);
				plane.visible = false;
				scene.add( plane );

				//Cria e Inicializa o renderer.
				renderer = new THREE.WebGLRenderer( { antialias: true } );
				renderer.setClearColor( 0x000000 );
				renderer.setPixelRatio( window.devicePixelRatio );
				renderer.setSize( window.innerWidth, window.innerHeight );
				renderer.sortObjects = false;
				renderer.shadowMapEnabled = true;
				renderer.shadowMapType = THREE.PCFShadowMap;
				container.appendChild( renderer.domElement );

				//Adiciona Eventos para o renderer
				renderer.domElement.addEventListener( 'mousemove', onDocumentMouseMove, false );
				renderer.domElement.addEventListener( 'mousedown', onDocumentMouseDown, false );
				renderer.domElement.addEventListener( 'mouseup', onDocumentMouseUp, false );

				//Adicona Eventos para a Janela
				window.addEventListener( 'resize', onWindowResize, false );
			}

			//Função que trata o redimencionamento da janela.
			function onWindowResize() {

				camera.aspect = window.innerWidth / window.innerHeight;
				camera.updateProjectionMatrix();

				renderer.setSize( window.innerWidth, window.innerHeight );

			}


			//Essa função é responsavel por tratar o evento de quanto o mouse está em cima do objeto.
			function onDocumentMouseMove( event ) {

				//Pega as posições do mouse e cria um raycaster entre o mouse e a camera.
				event.preventDefault();

				mouse.x = ( event.clientX / window.innerWidth ) * 2 - 1;
				mouse.y = - ( event.clientY / window.innerHeight ) * 2 + 1;

				raycaster.setFromCamera( mouse, camera );

				//Se algum objeto estiver selecionado, mude a posição em um eixo (neste caso o z) do objeto para a pos do mouse.
				if ( SELECTED ) {

					var intersects = raycaster.intersectObject( plane );
					//SELECTED.position.copy( intersects[ 0 ].point.sub( offset ) );
					SELECTED.position.x = intersects [ 0 ].point.x;
					SELECTED.position.z = intersects [ 0 ].point.z;
					console.log(SELECTED.position.x);
					console.log(SELECTED.position.z);
					return;

				}

				//Se algum objeto da cena (diferente do plano) intersecta com o mouse, muda o estilo de ponteiro para 'pointer', não entramos no 3º if.
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

				//Se não, a variavel INTERSECTED vai para null e o ponteiro volta ao normal.
				} else {

					if ( INTERSECTED ) INTERSECTED.material.color.setHex( INTERSECTED.currentHex );

					INTERSECTED = null;

					container.style.cursor = 'auto';

				}

			}

			//Função Responsavel por tratar eventos de "MouseDown".
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

			//Função responsavel por tratar eventos "MouseUp"
			function onDocumentMouseUp( event ) {

				event.preventDefault();

				controls.enabled = true;

				if ( INTERSECTED ) {

					plane.position.copy( INTERSECTED.position.x );

					SELECTED = null;

				}

				container.style.cursor = 'auto';

			}

			//Função que cria a animação da Cena e mostra a posição do mesh no eixo z (neste caso).
			function animate() {

				requestAnimationFrame( animate );
				render();

			}

			//Função que representa o render.
			function render() {

				controls.update();

				renderer.render( scene, camera );

			}

		</script>
	</body>
</html>