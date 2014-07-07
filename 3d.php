<?php
	/****** MINECRAFT 3D Skin Generator *****
	 * The contents of this project were first developed by Pierre Gros on 17th April 2012.
	 * It has once been modified by Carlos Ferreira (http://www.carlosferreira.me) on 31st May 2014.
	 * Translations done by Carlos Ferreira.
	 * Later adapted by Gijs "Gyzie" Oortgiese (http://www.gijsoortgiese.com/). Started on the 6st of July 2014.
	 * Fixing various issues.
	 *
	 **** GET Parameters ****
	 * user - Minecraft's username for the skin to be rendered.
	 * vr - Vertical Rotation.
	 * hr - Horizontal Rotation.
	 *
	 * hrh - Horizontal Rotation of the Head.
	 *
	 * vrll - Vertical Rotation of the Left Leg.
	 * vrrl - Vertical Rotation of the Right Leg.
	 * vrla - Vertical Rotation of the Left Arm.
	 * vrra - Vertical Rotation of the Right Arm.
	 *
	 * displayHair - Either or not to display hairs. Set to "false" to NOT display hairs.
	 * headOnly - Either or not to display the ONLY the head. Set to "true" to display ONLY the head (and the hair, based on displayHair).
	 *
	 * format - The format in which the image is to be rendered. PNG ("png") is used by default set to "svg" to use a vector version.
	 * ratio - The size of the "png" image. The default and minimum value is 2.
	 * 
	 * aa - Image smooting, false by default.
	 */
	 
	error_reporting( E_ERROR );
	
	$seconds_to_cache = 60 * 60 * 24 * 7; // Cache duration sent to the browser.
	$fallback_img = 'char.png'; // Use a not found skin whenever something goes wrong.
	
	function microtime_float() {
		list( $usec, $sec ) = explode( " ", microtime() );
		return ( (float) $usec + (float) $sec );
	}
	
	/* Function creates a blank canvas
	 * with transparancy with the size of the
	 * given image.
	 * 
	 * Espects canvas with and canvast height.
	 * Returns a empty canvas.
	 */
	function createEmptyCanvas($w, $h) {
		$dst = imagecreatetruecolor($w, $h);
		imagesavealpha($dst, true);
		$trans_colour = imagecolorallocatealpha($dst, 255, 255, 255, 127);
		imagefill($dst, 0, 0, $trans_colour);
		
		return $dst;
	}
	
	/* Function converts a non true color image to
	 * true color. This fixes the dark blue skins.
	 * 
	 * Espects an image.
	 * Returns a true color image.
	 */
	function convertToTrueColor($img) {
		if(imageistruecolor($img)) {
			return $img;
		}

		$dst = createEmptyCanvas(imagesx($img), imagesy($img));
	
		imagecopy($dst, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
		imagedestroy($img);

		return $dst;
	}
	
	/* Function converts a 1.8 skin (which is not supported by
	 * the script) to the old skin format.
	 * 
	 * Espects an image.
	 * Returns a croped image.
	 */
	function cropToOldSkinFormat($img) {
		if($width !== $height) {
			return $img;
		}

		$newWidth = imagesx($img);
		$newHeight = $newWidth / 2;
		
		$newImgPng = createEmptyCanvas($newWidth, $newHeight);
		
		imagecopy($newImgPng, $img, 0, 0, 0, 0, $newWidth, $newHeight);
		imagedestroy($img);
		
		return $newImgPng;
	}
	
	/* Function fixes issues with images that have a solid background
	 * 
	 * Espects an tru color image.
	 * Returns an image.
	 */
	function makeBackgroundTransparent($img) {
		// check if the corner box is one solid color
		$tempValue = null;
		
		for ($iH = 0; $iH < 8; $iH++) {
			for ($iV = 0; $iV < 8; $iV++) {
				$pixelColor = imagecolorat($img, $iH, $iV);

				$indexColor = imagecolorsforindex($img, $pixelColor);
				if($indexColor['alpha'] > 120) {
					// the image contains transparancy, noting to do
					return $img;
				}
				
				if($tempValue === null) {
					$tempValue = $pixelColor;
				} else if ($tempValue != $pixelColor){
					// Cannot determine a background color, file is probably fine
					return $img;
				}
			}
		}
		
		// the entire block is one solid color. Use this color to clear the background.
		$r = ($tempValue >> 16) & 0xFF;
		$g = ($tempValue >> 8) & 0xFF;
		$b = $tempValue & 0xFF;
		
			
		//imagealphablending($dst, true);
		imagesavealpha($img, false);
		$transparant = imagecolorallocate($img, $r, $g, $b);
		imagecolortransparent($img, $transparant);
		
		$imgX = imagesx($img);
		$imgY = imagesy($img);
		
		$dst = imagecreatetruecolor($imgX, $imgY);
		imagesavealpha($dst, true);
		$trans_colour = imagecolorallocatealpha($dst, $r, $g, $b, 127);
		imagefill($dst, 0, 0, $trans_colour);
		
		// fill the areas that should not be transparant		
		// create fill
		$color = imagecolorallocate($dst, $r, $g, $b);
		$positionMultiply = $imgX / 64;
		
		// head
		imagefilledrectangle($dst, 8*$positionMultiply, 0*$positionMultiply, 23*$positionMultiply, 7*$positionMultiply, $color);
		imagefilledrectangle($dst, 0*$positionMultiply, 8*$positionMultiply, 31*$positionMultiply, 15*$positionMultiply, $color);
		
		// right leg, body, right arm
		imagefilledrectangle($dst, 4*$positionMultiply, 16*$positionMultiply, 11*$positionMultiply, 19*$positionMultiply, $color);
		imagefilledrectangle($dst, 20*$positionMultiply, 16*$positionMultiply, 35*$positionMultiply, 19*$positionMultiply, $color);
		imagefilledrectangle($dst, 44*$positionMultiply, 16*$positionMultiply, 51*$positionMultiply, 19*$positionMultiply, $color);
		imagefilledrectangle($dst, 0*$positionMultiply, 20*$positionMultiply, 54*$positionMultiply, 31*$positionMultiply, $color);
		
		// left leg, left arm
		imagefilledrectangle($dst, 20*$positionMultiply, 48*$positionMultiply, 27*$positionMultiply, 51*$positionMultiply, $color);
		imagefilledrectangle($dst, 36*$positionMultiply, 48*$positionMultiply, 43*$positionMultiply, 51*$positionMultiply, $color);
		imagefilledrectangle($dst, 16*$positionMultiply, 52*$positionMultiply, 47*$positionMultiply, 63*$positionMultiply, $color);
		
		imagecopy($dst, $img, 0, 0, 0, 0, $imgX, $imgY);
		
		imagedestroy($img);
		
		return $dst;
	}
	
	/* Function converts the old _GET names to
	 * the new names. This makes it still compatable
	 * with scrips using the old names.
	 * 
	 * Espects the English _GET name.
	 * Returns the _GET value or the default value.
	 * Return false if the _GET is not found.
	 */
	function grabGetValue($name) {
		$parameters = array('user' => array('old' => 'login', 'default' => 'char'),
							'vr' => array('old' => 'a', 'default' => '-25'),
							'hr' => array('old' => 'w', 'default' => '35'),
							'hrh' => array('old' => 'wt', 'default' => '0'),
							'vrll' => array('old' => 'ajg', 'default' => '0'),
							'vrrl' => array('old' => 'ajd', 'default' => '0'),
							'vrla' => array('old' => 'abg', 'default' => '0'),
							'vrra' => array('old' => 'abd', 'default' => '0'),
							'displayHair' => array('old' => 'displayHairs', 'default' => 'true'),
							'headOnly' => array('old' => 'headOnly', 'default' => 'false'),
							'format' => array('old' => 'format', 'default' => 'png'),
							'ratio' => array('old' => 'ratio', 'default' => '12'),
							'aa' => array('old' => 'aa', 'default' => 'false'),
							);
		
		if(array_key_exists($name, $parameters)) {
			if(isset($_GET[$name])) {
				return $_GET[$name];
			} else if (isset($_GET[$parameters[$name]['old']])) {
				return $_GET[$parameters[$name]['old']];
			}
			return $parameters[$name]['default'];
		}
		
		return false;
	}
	
	/* ============
	 * Script Start
	 * ============
	 */
	$times = array(
		 array(
			 'Start',
			microtime_float() 
		) 
	);
	
	$username = grabGetValue('user');
	if (trim($username) == '') {
		$img_png = imageCreateFromPng($fallback_img);
	} else {
		$img_png = imageCreateFromPng('http://s3.amazonaws.com/MinecraftSkins/' . $username . '.png');
	}
	
	if (!$img_png) {
		// Player skin does not exist
		$img_png = imageCreateFromPng( $fallback_img );
	}
	
	if (!($width == $height * 2) || $height % 32 != 0) {
		// Bad ratio created
		$img_png = imageCreateFromPng( $fallback_img );
	}
	
	// makeBackgroundTransparent
	$img_png = makeBackgroundTransparent($img_png);
	// crop the image if it's a 1.8 skin.
	$img_png = cropToOldSkinFormat($img_png);
	// Convert the image to true color if not a true color image
	$img_png = convertToTrueColor($img_png);
	
	$width = imagesx($img_png);
	$height = imagesy($img_png);
	
	$hd_ratio = $height / 32; // Set HD ratio to 2 if the skin is 128x64
	
	$times[] = array(
		 'Download-Image',
		microtime_float() 
	);
	
	$vR = grabGetValue('vr');
	$hR = grabGetValue('hr');
	$head_only = ( grabGetValue('headOnly') == 'true' );
	$display_hair = ( grabGetValue('displayHair') != 'false' );
	$aa = ( grabGetValue('aa') == 'true' );
	
	// Rotation variables in radians (3D Rendering)
	$alpha = deg2rad( $vR ); // Vertical rotation on the X axis.
	$omega = deg2rad( $hR ); // Horizontal rotation on the Y axis.
	
	// Cosine and Sine values
	$cos_alpha = cos( $alpha );
	$sin_alpha = sin( $alpha );
	$cos_omega = cos( $omega );
	$sin_omega = sin( $omega );
	
	$members_angles = array(); // Head, Helmet, Torso, Arms, Legs
	$members_angles[ 'torso' ] = array(
		 'cos_alpha' => cos( 0 ),
		'sin_alpha' => sin( 0 ),
		'cos_omega' => cos( 0 ),
		'sin_omega' => sin( 0 ) 
	);
	
	$alpha_head = 0;
	$omega_head = deg2rad( grabGetValue('hrh') );
	$members_angles[ 'head' ] = array(
		 'cos_alpha' => cos( $alpha_head ),
		'sin_alpha' => sin( $alpha_head ),
		'cos_omega' => cos( $omega_head ),
		'sin_omega' => sin( $omega_head ) 
	);
	
	$members_angles[ 'helmet' ] = array(
		 'cos_alpha' => cos( $alpha_head ),
		'sin_alpha' => sin( $alpha_head ),
		'cos_omega' => cos( $omega_head ),
		'sin_omega' => sin( $omega_head ) 
	);
	
	$alpha_right_arm = deg2rad( grabGetValue('vrra') );
	$omega_right_arm = 0;
	$members_angles[ 'rightArm' ] = array(
		 'cos_alpha' => cos( $alpha_right_arm ),
		'sin_alpha' => sin( $alpha_right_arm ),
		'cos_omega' => cos( $omega_right_arm ),
		'sin_omega' => sin( $omega_right_arm ) 
	);
	
	$alpha_left_arm = deg2rad( grabGetValue('vrla') );
	$omega_left_arm = 0;
	$members_angles[ 'leftArm' ] = array(
		 'cos_alpha' => cos( $alpha_left_arm ),
		'sin_alpha' => sin( $alpha_left_arm ),
		'cos_omega' => cos( $omega_left_arm ),
		'sin_omega' => sin( $omega_left_arm ) 
	);
	
	$alpha_right_leg = deg2rad( grabGetValue('vrrl') );
	$omega_right_leg = 0;
	$members_angles[ 'rightLeg' ] = array(
		 'cos_alpha' => cos( $alpha_right_leg ),
		'sin_alpha' => sin( $alpha_right_leg ),
		'cos_omega' => cos( $omega_right_leg ),
		'sin_omega' => sin( $omega_right_leg ) 
	);
	
	$alpha_left_leg = deg2rad( grabGetValue('vrll') );
	$omega_left_leg = 0;
	$members_angles[ 'leftLeg' ] = array(
		 'cos_alpha' => cos( $alpha_left_leg ),
		'sin_alpha' => sin( $alpha_left_leg ),
		'cos_omega' => cos( $omega_left_leg ),
		'sin_omega' => sin( $omega_left_leg ) 
	);
	
	$minX = 0;
	$maxX = 0;
	$minY = 0;
	$maxY = 0;
	
	$times[] = array(
		 'Angle-Calculations',
		microtime_float() 
	);
	
	$visible_faces_format = array(
		 'front' => array(),
		'back' => array ()
	);
	
	$visible_faces = array(
		 'head' => $visible_faces_format,
		'torso' => $visible_faces_format,
		'rightArm' => $visible_faces_format,
		'leftArm' => $visible_faces_format,
		'rightLeg' => $visible_faces_format,
		'leftLeg' => $visible_faces_format 
	);
	
	$all_faces = array(
		 'back',
		'right',
		'top',
		'front',
		'left',
		'bottom' 
	);
	
	// Loop each preProject and Project then calculate the visible faces for each - also display
	foreach ( $visible_faces as $k => &$v ) {
		unset( $cube_max_depth_faces, $cube_points );
		$cube_points   = array();
		$cube_points[] = array(
			 new Point( array(
				 'x' => 0,
				'y' => 0,
				'z' => 0 
			) ),
			array(
				 'back',
				'right',
				'top' 
			) 
		); // 0
		$cube_points[] = array(
			 new Point( array(
				 'x' => 0,
				'y' => 0,
				'z' => 1 
			) ),
			array(
				 'front',
				'right',
				'top' 
			) 
		); // 1
		$cube_points[] = array(
			 new Point( array(
				 'x' => 0,
				'y' => 1,
				'z' => 0 
			) ),
			array(
				 'back',
				'right',
				'bottom' 
			) 
		); // 2
		$cube_points[] = array(
			 new Point( array(
				 'x' => 0,
				'y' => 1,
				'z' => 1 
			) ),
			array(
				 'front',
				'right',
				'bottom' 
			) 
		); // 3
		$cube_points[] = array(
			 new Point( array(
				 'x' => 1,
				'y' => 0,
				'z' => 0 
			) ),
			array(
				 'back',
				'left',
				'top' 
			) 
		); // 4
		$cube_points[] = array(
			 new Point( array(
				 'x' => 1,
				'y' => 0,
				'z' => 1 
			) ),
			array(
				 'front',
				'left',
				'top' 
			) 
		); // 5
		$cube_points[] = array(
			 new Point( array(
				 'x' => 1,
				'y' => 1,
				'z' => 0 
			) ),
			array(
				 'back',
				'left',
				'bottom' 
			) 
		); // 6
		$cube_points[] = array(
			 new Point( array(
				 'x' => 1,
				'y' => 1,
				'z' => 1 
			) ),
			array(
				 'front',
				'left',
				'bottom' 
			) 
		); // 7
		foreach ( $cube_points as $cube_point ) {
			$cube_point[ 0 ]->preProject( 0, 0, 0, $members_angles[ $k ][ 'cos_alpha' ], $members_angles[ $k ][ 'sin_alpha' ], $members_angles[ $k ][ 'cos_omega' ], $members_angles[ $k ][ 'sin_omega' ] );
			$cube_point[ 0 ]->project();
			if ( !isset( $cube_max_depth_faces ) )
				$cube_max_depth_faces = $cube_point;
			elseif ( $cube_max_depth_faces[ 0 ]->getDepth() > $cube_point[ 0 ]->getDepth() ) {
				$cube_max_depth_faces = $cube_point;
			}
		}
		$v[ 'back' ]  = $cube_max_depth_faces[ 1 ];
		$v[ 'front' ] = array_diff( $all_faces, $v[ 'back' ] );
	}
	
	$cube_points   = array();
	$cube_points[] = array(
		 new Point( array(
			 'x' => 0,
			'y' => 0,
			'z' => 0 
		) ),
		array(
			 'back',
			'right',
			'top' 
		) 
	); // 0
	$cube_points[] = array(
		 new Point( array(
			 'x' => 0,
			'y' => 0,
			'z' => 1 
		) ),
		array(
			 'front',
			'right',
			'top' 
		) 
	); // 1
	$cube_points[] = array(
		 new Point( array(
			 'x' => 0,
			'y' => 1,
			'z' => 0 
		) ),
		array(
			 'back',
			'right',
			'bottom' 
		) 
	); // 2
	$cube_points[] = array(
		 new Point( array(
			 'x' => 0,
			'y' => 1,
			'z' => 1 
		) ),
		array(
			 'front',
			'right',
			'bottom' 
		) 
	); // 3
	$cube_points[] = array(
		 new Point( array(
			 'x' => 1,
			'y' => 0,
			'z' => 0 
		) ),
		array(
			 'back',
			'left',
			'top' 
		) 
	); // 4
	$cube_points[] = array(
		 new Point( array(
			 'x' => 1,
			'y' => 0,
			'z' => 1 
		) ),
		array(
			 'front',
			'left',
			'top' 
		) 
	); // 5
	$cube_points[] = array(
		 new Point( array(
			 'x' => 1,
			'y' => 1,
			'z' => 0 
		) ),
		array(
			 'back',
			'left',
			'bottom' 
		) 
	); // 6
	$cube_points[] = array(
		 new Point( array(
			 'x' => 1,
			'y' => 1,
			'z' => 1 
		) ),
		array(
			 'front',
			'left',
			'bottom' 
		) 
	); // 7
	
	unset( $cube_max_depth_faces );
	foreach ( $cube_points as $cube_point ) {
		$cube_point[ 0 ]->project();
		if ( !isset( $cube_max_depth_faces ) )
			$cube_max_depth_faces = $cube_point;
		elseif ( $cube_max_depth_faces[ 0 ]->getDepth() > $cube_point[ 0 ]->getDepth() ) {
			$cube_max_depth_faces = $cube_point;
		}
	}
	
	$back_faces       = $cube_max_depth_faces[ 1 ];
	$front_faces      = array_diff( $all_faces, $back_faces );
	
	$times[]          = array(
		 'Determination-of-faces',
		microtime_float() 
	);
	
	$depths_of_face = array();
	$polygons = array();
	$cube_faces_array = array(
		 'front' => array(),
		'back' => array(),
		'top' => array(),
		'bottom' => array(),
		'right' => array(),
		'left' => array ()
	);
	
	$polygons = array(
		'helmet' => $cube_faces_array,
		'head' => $cube_faces_array,
		'torso' => $cube_faces_array,
		'rightArm' => $cube_faces_array,
		'leftArm' => $cube_faces_array,
		'rightLeg' => $cube_faces_array,
		'leftLeg' => $cube_faces_array 
	);
	
	// HEAD
	for ( $i = 0; $i < 9 * $hd_ratio; $i++ ) {
		for ( $j = 0; $j < 9 * $hd_ratio; $j++ ) {
			if ( !isset( $volume_points[ $i ][ $j ][ -2 * $hd_ratio ] ) ) {
				$volume_points[ $i ][ $j ][ -2 * $hd_ratio ] = new Point( array(
					 'x' => $i,
					'y' => $j,
					'z' => -2 * $hd_ratio 
				) );
			}
			if ( !isset( $volume_points[ $i ][ $j ][ 6 * $hd_ratio ] ) ) {
				$volume_points[ $i ][ $j ][ 6 * $hd_ratio ] = new Point( array(
					 'x' => $i,
					'y' => $j,
					'z' => 6 * $hd_ratio 
				) );
			}
		}
	}
	for ( $j = 0; $j < 9 * $hd_ratio; $j++ ) {
		for ( $k = -2 * $hd_ratio; $k < 7 * $hd_ratio; $k++ ) {
			if ( !isset( $volume_points[ 0 ][ $j ][ $k ] ) ) {
				$volume_points[ 0 ][ $j ][ $k ] = new Point( array(
					 'x' => 0,
					'y' => $j,
					'z' => $k 
				) );
			}
			if ( !isset( $volume_points[ 8 * $hd_ratio ][ $j ][ $k ] ) ) {
				$volume_points[ 8 * $hd_ratio ][ $j ][ $k ] = new Point( array(
					 'x' => 8 * $hd_ratio,
					'y' => $j,
					'z' => $k 
				) );
			}
		}
	}
	for ( $i = 0; $i < 9 * $hd_ratio; $i++ ) {
		for ( $k = -2 * $hd_ratio; $k < 7 * $hd_ratio; $k++ ) {
			if ( !isset( $volume_points[ $i ][ 0 ][ $k ] ) ) {
				$volume_points[ $i ][ 0 ][ $k ] = new Point( array(
					 'x' => $i,
					'y' => 0,
					'z' => $k 
				) );
			}
			if ( !isset( $volume_points[ $i ][ 8 * $hd_ratio ][ $k ] ) ) {
				$volume_points[ $i ][ 8 * $hd_ratio ][ $k ] = new Point( array(
					 'x' => $i,
					'y' => 8 * $hd_ratio,
					'z' => $k 
				) );
			}
		}
	}
	for ( $i = 0; $i < 8 * $hd_ratio; $i++ ) {
		for ( $j = 0; $j < 8 * $hd_ratio; $j++ ) {
			$polygons[ 'head' ][ 'back' ][]  = new Polygon( array(
				 $volume_points[ $i ][ $j ][ -2 * $hd_ratio ],
				$volume_points[ $i + 1 ][ $j ][ -2 * $hd_ratio ],
				$volume_points[ $i + 1 ][ $j + 1 ][ -2 * $hd_ratio ],
				$volume_points[ $i ][ $j + 1 ][ -2 * $hd_ratio ] 
			), imagecolorat( $img_png, ( 32 * $hd_ratio - 1 ) - $i, 8 * $hd_ratio + $j ) );
			$polygons[ 'head' ][ 'front' ][] = new Polygon( array(
				 $volume_points[ $i ][ $j ][ 6 * $hd_ratio ],
				$volume_points[ $i + 1 ][ $j ][ 6 * $hd_ratio ],
				$volume_points[ $i + 1 ][ $j + 1 ][ 6 * $hd_ratio ],
				$volume_points[ $i ][ $j + 1 ][ 6 * $hd_ratio ] 
			), imagecolorat( $img_png, 8 * $hd_ratio + $i, 8 * $hd_ratio + $j ) );
		}
	}
	for ( $j = 0; $j < 8 * $hd_ratio; $j++ ) {
		for ( $k = -2 * $hd_ratio; $k < 6 * $hd_ratio; $k++ ) {
			$polygons[ 'head' ][ 'right' ][] = new Polygon( array(
				 $volume_points[ 0 ][ $j ][ $k ],
				$volume_points[ 0 ][ $j ][ $k + 1 ],
				$volume_points[ 0 ][ $j + 1 ][ $k + 1 ],
				$volume_points[ 0 ][ $j + 1 ][ $k ] 
			), imagecolorat( $img_png, $k + 2 * $hd_ratio, 8 * $hd_ratio + $j ) );
			$polygons[ 'head' ][ 'left' ][]  = new Polygon( array(
				 $volume_points[ 8 * $hd_ratio ][ $j ][ $k ],
				$volume_points[ 8 * $hd_ratio ][ $j ][ $k + 1 ],
				$volume_points[ 8 * $hd_ratio ][ $j + 1 ][ $k + 1 ],
				$volume_points[ 8 * $hd_ratio ][ $j + 1 ][ $k ] 
			), imagecolorat( $img_png, ( 24 * $hd_ratio - 1 ) - $k - 2 * $hd_ratio, 8 * $hd_ratio + $j ) );
		}
	}
	for ( $i = 0; $i < 8 * $hd_ratio; $i++ ) {
		for ( $k = -2 * $hd_ratio; $k < 6 * $hd_ratio; $k++ ) {
			$polygons[ 'head' ][ 'top' ][]    = new Polygon( array(
				 $volume_points[ $i ][ 0 ][ $k ],
				$volume_points[ $i + 1 ][ 0 ][ $k ],
				$volume_points[ $i + 1 ][ 0 ][ $k + 1 ],
				$volume_points[ $i ][ 0 ][ $k + 1 ] 
			), imagecolorat( $img_png, 8 * $hd_ratio + $i, $k + 2 * $hd_ratio ) );
			$polygons[ 'head' ][ 'bottom' ][] = new Polygon( array(
				 $volume_points[ $i ][ 8 * $hd_ratio ][ $k ],
				$volume_points[ $i + 1 ][ 8 * $hd_ratio ][ $k ],
				$volume_points[ $i + 1 ][ 8 * $hd_ratio ][ $k + 1 ],
				$volume_points[ $i ][ 8 * $hd_ratio ][ $k + 1 ] 
			), imagecolorat( $img_png, 16 * $hd_ratio + $i, ( 8 * $hd_ratio - 1 ) - ( $k + 2 * $hd_ratio ) ) );
		}
	}
	if ( $display_hair ) {
		// HELMET/HAIR
		$volume_points = array();
		for ( $i = 0; $i < 9 * $hd_ratio; $i++ ) {
			for ( $j = 0; $j < 9 * $hd_ratio; $j++ ) {
				if ( !isset( $volume_points[ $i ][ $j ][ -2 * $hd_ratio ] ) ) {
					$volume_points[ $i ][ $j ][ -2 * $hd_ratio ] = new Point( array(
						 'x' => $i * 9 / 8 - 0.5 * $hd_ratio,
						'y' => $j * 9 / 8 - 0.5 * $hd_ratio,
						'z' => -2.5 * $hd_ratio 
					) );
				}
				if ( !isset( $volume_points[ $i ][ $j ][ 6 * $hd_ratio ] ) ) {
					$volume_points[ $i ][ $j ][ 6 * $hd_ratio ] = new Point( array(
						 'x' => $i * 9 / 8 - 0.5 * $hd_ratio,
						'y' => $j * 9 / 8 - 0.5 * $hd_ratio,
						'z' => 6.5 * $hd_ratio 
					) );
				}
			}
		}
		for ( $j = 0; $j < 9 * $hd_ratio; $j++ ) {
			for ( $k = -2 * $hd_ratio; $k < 7 * $hd_ratio; $k++ ) {
				if ( !isset( $volume_points[ 0 ][ $j ][ $k ] ) ) {
					$volume_points[ 0 ][ $j ][ $k ] = new Point( array(
						 'x' => -0.5 * $hd_ratio,
						'y' => $j * 9 / 8 - 0.5 * $hd_ratio,
						'z' => $k * 9 / 8 - 0.5 * $hd_ratio 
					) );
				}
				if ( !isset( $volume_points[ 8 * $hd_ratio ][ $j ][ $k ] ) ) {
					$volume_points[ 8 * $hd_ratio ][ $j ][ $k ] = new Point( array(
						 'x' => 8.5 * $hd_ratio,
						'y' => $j * 9 / 8 - 0.5 * $hd_ratio,
						'z' => $k * 9 / 8 - 0.5 * $hd_ratio 
					) );
				}
			}
		}
		for ( $i = 0; $i < 9 * $hd_ratio; $i++ ) {
			for ( $k = -2 * $hd_ratio; $k < 7 * $hd_ratio; $k++ ) {
				if ( !isset( $volume_points[ $i ][ 0 ][ $k ] ) ) {
					$volume_points[ $i ][ 0 ][ $k ] = new Point( array(
						 'x' => $i * 9 / 8 - 0.5 * $hd_ratio,
						'y' => -0.5 * $hd_ratio,
						'z' => $k * 9 / 8 - 0.5 * $hd_ratio 
					) );
				}
				if ( !isset( $volume_points[ $i ][ 8 * $hd_ratio ][ $k ] ) ) {
					$volume_points[ $i ][ 8 * $hd_ratio ][ $k ] = new Point( array(
						 'x' => $i * 9 / 8 - 0.5 * $hd_ratio,
						'y' => 8.5 * $hd_ratio,
						'z' => $k * 9 / 8 - 0.5 * $hd_ratio 
					) );
				}
			}
		}
		for ( $i = 0; $i < 8 * $hd_ratio; $i++ ) {
			for ( $j = 0; $j < 8 * $hd_ratio; $j++ ) {
				$polygons[ 'helmet' ][ 'back' ][]  = new Polygon( array(
					 $volume_points[ $i ][ $j ][ -2 * $hd_ratio ],
					$volume_points[ $i + 1 ][ $j ][ -2 * $hd_ratio ],
					$volume_points[ $i + 1 ][ $j + 1 ][ -2 * $hd_ratio ],
					$volume_points[ $i ][ $j + 1 ][ -2 * $hd_ratio ] 
				), imagecolorat( $img_png, 32 * $hd_ratio + ( 32 * $hd_ratio - 1 ) - $i, 8 * $hd_ratio + $j ) );
				$polygons[ 'helmet' ][ 'front' ][] = new Polygon( array(
					 $volume_points[ $i ][ $j ][ 6 * $hd_ratio ],
					$volume_points[ $i + 1 ][ $j ][ 6 * $hd_ratio ],
					$volume_points[ $i + 1 ][ $j + 1 ][ 6 * $hd_ratio ],
					$volume_points[ $i ][ $j + 1 ][ 6 * $hd_ratio ] 
				), imagecolorat( $img_png, 32 * $hd_ratio + 8 * $hd_ratio + $i, 8 * $hd_ratio + $j ) );
			}
		}
		for ( $j = 0; $j < 8 * $hd_ratio; $j++ ) {
			for ( $k = -2 * $hd_ratio; $k < 6 * $hd_ratio; $k++ ) {
				$polygons[ 'helmet' ][ 'right' ][] = new Polygon( array(
					 $volume_points[ 0 ][ $j ][ $k ],
					$volume_points[ 0 ][ $j ][ $k + 1 ],
					$volume_points[ 0 ][ $j + 1 ][ $k + 1 ],
					$volume_points[ 0 ][ $j + 1 ][ $k ] 
				), imagecolorat( $img_png, 32 * $hd_ratio + $k + 2 * $hd_ratio, 8 * $hd_ratio + $j ) );
				$polygons[ 'helmet' ][ 'left' ][]  = new Polygon( array(
					 $volume_points[ 8 * $hd_ratio ][ $j ][ $k ],
					$volume_points[ 8 * $hd_ratio ][ $j ][ $k + 1 ],
					$volume_points[ 8 * $hd_ratio ][ $j + 1 ][ $k + 1 ],
					$volume_points[ 8 * $hd_ratio ][ $j + 1 ][ $k ] 
				), imagecolorat( $img_png, 32 * $hd_ratio + ( 24 * $hd_ratio - 1 ) - $k - 2 * $hd_ratio, 8 * $hd_ratio + $j ) );
			}
		}
		for ( $i = 0; $i < 8 * $hd_ratio; $i++ ) {
			for ( $k = -2 * $hd_ratio; $k < 6 * $hd_ratio; $k++ ) {
				$polygons[ 'helmet' ][ 'top' ][]    = new Polygon( array(
					 $volume_points[ $i ][ 0 ][ $k ],
					$volume_points[ $i + 1 ][ 0 ][ $k ],
					$volume_points[ $i + 1 ][ 0 ][ $k + 1 ],
					$volume_points[ $i ][ 0 ][ $k + 1 ] 
				), imagecolorat( $img_png, 32 * $hd_ratio + 8 * $hd_ratio + $i, $k + 2 * $hd_ratio ) );
				$polygons[ 'helmet' ][ 'bottom' ][] = new Polygon( array(
					 $volume_points[ $i ][ 8 * $hd_ratio ][ $k ],
					$volume_points[ $i + 1 ][ 8 * $hd_ratio ][ $k ],
					$volume_points[ $i + 1 ][ 8 * $hd_ratio ][ $k + 1 ],
					$volume_points[ $i ][ 8 * $hd_ratio ][ $k + 1 ] 
				), imagecolorat( $img_png, 32 * $hd_ratio + 16 * $hd_ratio + $i, ( 8 * $hd_ratio - 1 ) - ( $k + 2 * $hd_ratio ) ) );
			}
		}
	}
	if ( !$head_only ) {
		// TORSO
		$volume_points = array();
		for ( $i = 0; $i < 9 * $hd_ratio; $i++ ) {
			for ( $j = 0; $j < 13 * $hd_ratio; $j++ ) {
				if ( !isset( $volume_points[ $i ][ $j ][ 0 ] ) ) {
					$volume_points[ $i ][ $j ][ 0 ] = new Point( array(
						 'x' => $i,
						'y' => $j + 8 * $hd_ratio,
						'z' => 0 
					) );
				}
				if ( !isset( $volume_points[ $i ][ $j ][ 4 * $hd_ratio ] ) ) {
					$volume_points[ $i ][ $j ][ 4 * $hd_ratio ] = new Point( array(
						 'x' => $i,
						'y' => $j + 8 * $hd_ratio,
						'z' => 4 * $hd_ratio 
					) );
				}
			}
		}
		for ( $j = 0; $j < 13 * $hd_ratio; $j++ ) {
			for ( $k = 0; $k < 5 * $hd_ratio; $k++ ) {
				if ( !isset( $volume_points[ 0 ][ $j ][ $k ] ) ) {
					$volume_points[ 0 ][ $j ][ $k ] = new Point( array(
						 'x' => 0,
						'y' => $j + 8 * $hd_ratio,
						'z' => $k 
					) );
				}
				if ( !isset( $volume_points[ 8 * $hd_ratio ][ $j ][ $k ] ) ) {
					$volume_points[ 8 * $hd_ratio ][ $j ][ $k ] = new Point( array(
						 'x' => 8 * $hd_ratio,
						'y' => $j + 8 * $hd_ratio,
						'z' => $k 
					) );
				}
			}
		}
		for ( $i = 0; $i < 9 * $hd_ratio; $i++ ) {
			for ( $k = 0; $k < 5 * $hd_ratio; $k++ ) {
				if ( !isset( $volume_points[ $i ][ 0 ][ $k ] ) ) {
					$volume_points[ $i ][ 0 ][ $k ] = new Point( array(
						 'x' => $i,
						'y' => 0 + 8 * $hd_ratio,
						'z' => $k 
					) );
				}
				if ( !isset( $volume_points[ $i ][ 12 * $hd_ratio ][ $k ] ) ) {
					$volume_points[ $i ][ 12 * $hd_ratio ][ $k ] = new Point( array(
						 'x' => $i,
						'y' => 12 * $hd_ratio + 8 * $hd_ratio,
						'z' => $k 
					) );
				}
			}
		}
		for ( $i = 0; $i < 8 * $hd_ratio; $i++ ) {
			for ( $j = 0; $j < 12 * $hd_ratio; $j++ ) {
				$polygons[ 'torso' ][ 'back' ][]  = new Polygon( array(
					 $volume_points[ $i ][ $j ][ 0 ],
					$volume_points[ $i + 1 ][ $j ][ 0 ],
					$volume_points[ $i + 1 ][ $j + 1 ][ 0 ],
					$volume_points[ $i ][ $j + 1 ][ 0 ] 
				), imagecolorat( $img_png, ( 40 * $hd_ratio - 1 ) - $i, 20 * $hd_ratio + $j ) );
				$polygons[ 'torso' ][ 'front' ][] = new Polygon( array(
					 $volume_points[ $i ][ $j ][ 4 * $hd_ratio ],
					$volume_points[ $i + 1 ][ $j ][ 4 * $hd_ratio ],
					$volume_points[ $i + 1 ][ $j + 1 ][ 4 * $hd_ratio ],
					$volume_points[ $i ][ $j + 1 ][ 4 * $hd_ratio ] 
				), imagecolorat( $img_png, 20 * $hd_ratio + $i, 20 * $hd_ratio + $j ) );
			}
		}
		for ( $j = 0; $j < 12 * $hd_ratio; $j++ ) {
			for ( $k = 0; $k < 4 * $hd_ratio; $k++ ) {
				$polygons[ 'torso' ][ 'right' ][] = new Polygon( array(
					 $volume_points[ 0 ][ $j ][ $k ],
					$volume_points[ 0 ][ $j ][ $k + 1 ],
					$volume_points[ 0 ][ $j + 1 ][ $k + 1 ],
					$volume_points[ 0 ][ $j + 1 ][ $k ] 
				), imagecolorat( $img_png, 16 * $hd_ratio + $k, 20 * $hd_ratio + $j ) );
				$polygons[ 'torso' ][ 'left' ][]  = new Polygon( array(
					 $volume_points[ 8 * $hd_ratio ][ $j ][ $k ],
					$volume_points[ 8 * $hd_ratio ][ $j ][ $k + 1 ],
					$volume_points[ 8 * $hd_ratio ][ $j + 1 ][ $k + 1 ],
					$volume_points[ 8 * $hd_ratio ][ $j + 1 ][ $k ] 
				), imagecolorat( $img_png, ( 32 * $hd_ratio - 1 ) - $k, 20 * $hd_ratio + $j ) );
			}
		}
		for ( $i = 0; $i < 8 * $hd_ratio; $i++ ) {
			for ( $k = 0; $k < 4 * $hd_ratio; $k++ ) {
				$polygons[ 'torso' ][ 'top' ][]    = new Polygon( array(
					 $volume_points[ $i ][ 0 ][ $k ],
					$volume_points[ $i + 1 ][ 0 ][ $k ],
					$volume_points[ $i + 1 ][ 0 ][ $k + 1 ],
					$volume_points[ $i ][ 0 ][ $k + 1 ] 
				), imagecolorat( $img_png, 20 * $hd_ratio + $i, 16 * $hd_ratio + $k ) );
				$polygons[ 'torso' ][ 'bottom' ][] = new Polygon( array(
					 $volume_points[ $i ][ 12 * $hd_ratio ][ $k ],
					$volume_points[ $i + 1 ][ 12 * $hd_ratio ][ $k ],
					$volume_points[ $i + 1 ][ 12 * $hd_ratio ][ $k + 1 ],
					$volume_points[ $i ][ 12 * $hd_ratio ][ $k + 1 ] 
				), imagecolorat( $img_png, 28 * $hd_ratio + $i, ( 20 * $hd_ratio - 1 ) - $k ) );
			}
		}
		// RIGHT ARM
		$volume_points = array();
		for ( $i = 0; $i < 9 * $hd_ratio; $i++ ) {
			for ( $j = 0; $j < 13 * $hd_ratio; $j++ ) {
				if ( !isset( $volume_points[ $i ][ $j ][ 0 ] ) ) {
					$volume_points[ $i ][ $j ][ 0 ] = new Point( array(
						 'x' => $i - 4 * $hd_ratio,
						'y' => $j + 8 * $hd_ratio,
						'z' => 0 
					) );
				}
				if ( !isset( $volume_points[ $i ][ $j ][ 4 * $hd_ratio ] ) ) {
					$volume_points[ $i ][ $j ][ 4 * $hd_ratio ] = new Point( array(
						 'x' => $i - 4 * $hd_ratio,
						'y' => $j + 8 * $hd_ratio,
						'z' => 4 * $hd_ratio 
					) );
				}
			}
		}
		for ( $j = 0; $j < 13 * $hd_ratio; $j++ ) {
			for ( $k = 0; $k < 5 * $hd_ratio; $k++ ) {
				if ( !isset( $volume_points[ 0 ][ $j ][ $k ] ) ) {
					$volume_points[ 0 ][ $j ][ $k ] = new Point( array(
						 'x' => 0 - 4 * $hd_ratio,
						'y' => $j + 8 * $hd_ratio,
						'z' => $k 
					) );
				}
				if ( !isset( $volume_points[ 8 * $hd_ratio ][ $j ][ $k ] ) ) {
					$volume_points[ 4 * $hd_ratio ][ $j ][ $k ] = new Point( array(
						 'x' => 4 * $hd_ratio - 4 * $hd_ratio,
						'y' => $j + 8 * $hd_ratio,
						'z' => $k 
					) );
				}
			}
		}
		for ( $i = 0; $i < 9 * $hd_ratio; $i++ ) {
			for ( $k = 0; $k < 5 * $hd_ratio; $k++ ) {
				if ( !isset( $volume_points[ $i ][ 0 ][ $k ] ) ) {
					$volume_points[ $i ][ 0 ][ $k ] = new Point( array(
						 'x' => $i - 4 * $hd_ratio,
						'y' => 0 + 8 * $hd_ratio,
						'z' => $k 
					) );
				}
				if ( !isset( $volume_points[ $i ][ 12 * $hd_ratio ][ $k ] ) ) {
					$volume_points[ $i ][ 12 * $hd_ratio ][ $k ] = new Point( array(
						 'x' => $i - 4 * $hd_ratio,
						'y' => 12 * $hd_ratio + 8 * $hd_ratio,
						'z' => $k 
					) );
				}
			}
		}
		for ( $i = 0; $i < 4 * $hd_ratio; $i++ ) {
			for ( $j = 0; $j < 12 * $hd_ratio; $j++ ) {
				$polygons[ 'rightArm' ][ 'back' ][]  = new Polygon( array(
					 $volume_points[ $i ][ $j ][ 0 ],
					$volume_points[ $i + 1 ][ $j ][ 0 ],
					$volume_points[ $i + 1 ][ $j + 1 ][ 0 ],
					$volume_points[ $i ][ $j + 1 ][ 0 ] 
				), imagecolorat( $img_png, ( 56 * $hd_ratio - 1 ) - $i, 20 * $hd_ratio + $j ) );
				$polygons[ 'rightArm' ][ 'front' ][] = new Polygon( array(
					 $volume_points[ $i ][ $j ][ 4 * $hd_ratio ],
					$volume_points[ $i + 1 ][ $j ][ 4 * $hd_ratio ],
					$volume_points[ $i + 1 ][ $j + 1 ][ 4 * $hd_ratio ],
					$volume_points[ $i ][ $j + 1 ][ 4 * $hd_ratio ] 
				), imagecolorat( $img_png, 44 * $hd_ratio + $i, 20 * $hd_ratio + $j ) );
			}
		}
		for ( $j = 0; $j < 12 * $hd_ratio; $j++ ) {
			for ( $k = 0; $k < 4 * $hd_ratio; $k++ ) {
				$polygons[ 'rightArm' ][ 'right' ][] = new Polygon( array(
					 $volume_points[ 0 ][ $j ][ $k ],
					$volume_points[ 0 ][ $j ][ $k + 1 ],
					$volume_points[ 0 ][ $j + 1 ][ $k + 1 ],
					$volume_points[ 0 ][ $j + 1 ][ $k ] 
				), imagecolorat( $img_png, 40 * $hd_ratio + $k, 20 * $hd_ratio + $j ) );
				$polygons[ 'rightArm' ][ 'left' ][]  = new Polygon( array(
					 $volume_points[ 4 * $hd_ratio ][ $j ][ $k ],
					$volume_points[ 4 * $hd_ratio ][ $j ][ $k + 1 ],
					$volume_points[ 4 * $hd_ratio ][ $j + 1 ][ $k + 1 ],
					$volume_points[ 4 * $hd_ratio ][ $j + 1 ][ $k ] 
				), imagecolorat( $img_png, ( 52 * $hd_ratio - 1 ) - $k, 20 * $hd_ratio + $j ) );
			}
		}
		for ( $i = 0; $i < 4 * $hd_ratio; $i++ ) {
			for ( $k = 0; $k < 4 * $hd_ratio; $k++ ) {
				$polygons[ 'rightArm' ][ 'top' ][]    = new Polygon( array(
					 $volume_points[ $i ][ 0 ][ $k ],
					$volume_points[ $i + 1 ][ 0 ][ $k ],
					$volume_points[ $i + 1 ][ 0 ][ $k + 1 ],
					$volume_points[ $i ][ 0 ][ $k + 1 ] 
				), imagecolorat( $img_png, 44 * $hd_ratio + $i, 16 * $hd_ratio + $k ) );
				$polygons[ 'rightArm' ][ 'bottom' ][] = new Polygon( array(
					 $volume_points[ $i ][ 12 * $hd_ratio ][ $k ],
					$volume_points[ $i + 1 ][ 12 * $hd_ratio ][ $k ],
					$volume_points[ $i + 1 ][ 12 * $hd_ratio ][ $k + 1 ],
					$volume_points[ $i ][ 12 * $hd_ratio ][ $k + 1 ] 
				), imagecolorat( $img_png, 48 * $hd_ratio + $i, ( 20 * $hd_ratio - 1 ) - $k ) );
			}
		}
		// LEFT ARM
		$volume_points = array();
		for ( $i = 0; $i < 9 * $hd_ratio; $i++ ) {
			for ( $j = 0; $j < 13 * $hd_ratio; $j++ ) {
				if ( !isset( $volume_points[ $i ][ $j ][ 0 ] ) ) {
					$volume_points[ $i ][ $j ][ 0 ] = new Point( array(
						 'x' => $i + 8 * $hd_ratio,
						'y' => $j + 8 * $hd_ratio,
						'z' => 0 
					) );
				}
				if ( !isset( $volume_points[ $i ][ $j ][ 4 * $hd_ratio ] ) ) {
					$volume_points[ $i ][ $j ][ 4 * $hd_ratio ] = new Point( array(
						 'x' => $i + 8 * $hd_ratio,
						'y' => $j + 8 * $hd_ratio,
						'z' => 4 * $hd_ratio 
					) );
				}
			}
		}
		for ( $j = 0; $j < 13 * $hd_ratio; $j++ ) {
			for ( $k = 0; $k < 5 * $hd_ratio; $k++ ) {
				if ( !isset( $volume_points[ 0 ][ $j ][ $k ] ) ) {
					$volume_points[ 0 ][ $j ][ $k ] = new Point( array(
						 'x' => 0 + 8 * $hd_ratio,
						'y' => $j + 8 * $hd_ratio,
						'z' => $k 
					) );
				}
				if ( !isset( $volume_points[ 8 * $hd_ratio ][ $j ][ $k ] ) ) {
					$volume_points[ 4 * $hd_ratio ][ $j ][ $k ] = new Point( array(
						 'x' => 4 * $hd_ratio + 8 * $hd_ratio,
						'y' => $j + 8 * $hd_ratio,
						'z' => $k 
					) );
				}
			}
		}
		for ( $i = 0; $i < 9 * $hd_ratio; $i++ ) {
			for ( $k = 0; $k < 5 * $hd_ratio; $k++ ) {
				if ( !isset( $volume_points[ $i ][ 0 ][ $k ] ) ) {
					$volume_points[ $i ][ 0 ][ $k ] = new Point( array(
						 'x' => $i + 8 * $hd_ratio,
						'y' => 0 + 8 * $hd_ratio,
						'z' => $k 
					) );
				}
				if ( !isset( $volume_points[ $i ][ 12 * $hd_ratio ][ $k ] ) ) {
					$volume_points[ $i ][ 12 * $hd_ratio ][ $k ] = new Point( array(
						 'x' => $i + 8 * $hd_ratio,
						'y' => 12 * $hd_ratio + 8 * $hd_ratio,
						'z' => $k 
					) );
				}
			}
		}
		for ( $i = 0; $i < 4 * $hd_ratio; $i++ ) {
			for ( $j = 0; $j < 12 * $hd_ratio; $j++ ) {
				$polygons[ 'leftArm' ][ 'back' ][]  = new Polygon( array(
					 $volume_points[ $i ][ $j ][ 0 ],
					$volume_points[ $i + 1 ][ $j ][ 0 ],
					$volume_points[ $i + 1 ][ $j + 1 ][ 0 ],
					$volume_points[ $i ][ $j + 1 ][ 0 ] 
				), imagecolorat( $img_png, ( 56 * $hd_ratio - 1 ) - ( ( 4 * $hd_ratio - 1 ) - $i ), 20 * $hd_ratio + $j ) );
				$polygons[ 'leftArm' ][ 'front' ][] = new Polygon( array(
					 $volume_points[ $i ][ $j ][ 4 * $hd_ratio ],
					$volume_points[ $i + 1 ][ $j ][ 4 * $hd_ratio ],
					$volume_points[ $i + 1 ][ $j + 1 ][ 4 * $hd_ratio ],
					$volume_points[ $i ][ $j + 1 ][ 4 * $hd_ratio ] 
				), imagecolorat( $img_png, 44 * $hd_ratio + ( ( 4 * $hd_ratio - 1 ) - $i ), 20 * $hd_ratio + $j ) );
			}
		}
		for ( $j = 0; $j < 12 * $hd_ratio; $j++ ) {
			for ( $k = 0; $k < 4 * $hd_ratio; $k++ ) {
				$polygons[ 'leftArm' ][ 'right' ][] = new Polygon( array(
					 $volume_points[ 0 ][ $j ][ $k ],
					$volume_points[ 0 ][ $j ][ $k + 1 ],
					$volume_points[ 0 ][ $j + 1 ][ $k + 1 ],
					$volume_points[ 0 ][ $j + 1 ][ $k ] 
				), imagecolorat( $img_png, 40 * $hd_ratio + ( ( 4 * $hd_ratio - 1 ) - $k ), 20 * $hd_ratio + $j ) );
				$polygons[ 'leftArm' ][ 'left' ][]  = new Polygon( array(
					 $volume_points[ 4 * $hd_ratio ][ $j ][ $k ],
					$volume_points[ 4 * $hd_ratio ][ $j ][ $k + 1 ],
					$volume_points[ 4 * $hd_ratio ][ $j + 1 ][ $k + 1 ],
					$volume_points[ 4 * $hd_ratio ][ $j + 1 ][ $k ] 
				), imagecolorat( $img_png, ( 52 * $hd_ratio - 1 ) - ( ( 4 * $hd_ratio - 1 ) - $k ), 20 * $hd_ratio + $j ) );
			}
		}
		for ( $i = 0; $i < 4 * $hd_ratio; $i++ ) {
			for ( $k = 0; $k < 4 * $hd_ratio; $k++ ) {
				$polygons[ 'leftArm' ][ 'top' ][]    = new Polygon( array(
					 $volume_points[ $i ][ 0 ][ $k ],
					$volume_points[ $i + 1 ][ 0 ][ $k ],
					$volume_points[ $i + 1 ][ 0 ][ $k + 1 ],
					$volume_points[ $i ][ 0 ][ $k + 1 ] 
				), imagecolorat( $img_png, 44 * $hd_ratio + ( ( 4 * $hd_ratio - 1 ) - $i ), 16 * $hd_ratio + $k ) );
				$polygons[ 'leftArm' ][ 'bottom' ][] = new Polygon( array(
					 $volume_points[ $i ][ 12 * $hd_ratio ][ $k ],
					$volume_points[ $i + 1 ][ 12 * $hd_ratio ][ $k ],
					$volume_points[ $i + 1 ][ 12 * $hd_ratio ][ $k + 1 ],
					$volume_points[ $i ][ 12 * $hd_ratio ][ $k + 1 ] 
				), imagecolorat( $img_png, 48 * $hd_ratio + ( ( 4 * $hd_ratio - 1 ) - $i ), ( 20 * $hd_ratio - 1 ) - $k ) );
			}
		}
		// RIGHT LEG
		$volume_points = array();
		for ( $i = 0; $i < 9 * $hd_ratio; $i++ ) {
			for ( $j = 0; $j < 13 * $hd_ratio; $j++ ) {
				if ( !isset( $volume_points[ $i ][ $j ][ 0 ] ) ) {
					$volume_points[ $i ][ $j ][ 0 ] = new Point( array(
						 'x' => $i,
						'y' => $j + 20 * $hd_ratio,
						'z' => 0 
					) );
				}
				if ( !isset( $volume_points[ $i ][ $j ][ 4 * $hd_ratio ] ) ) {
					$volume_points[ $i ][ $j ][ 4 * $hd_ratio ] = new Point( array(
						 'x' => $i,
						'y' => $j + 20 * $hd_ratio,
						'z' => 4 * $hd_ratio 
					) );
				}
			}
		}
		for ( $j = 0; $j < 13 * $hd_ratio; $j++ ) {
			for ( $k = 0; $k < 5 * $hd_ratio; $k++ ) {
				if ( !isset( $volume_points[ 0 ][ $j ][ $k ] ) ) {
					$volume_points[ 0 ][ $j ][ $k ] = new Point( array(
						 'x' => 0,
						'y' => $j + 20 * $hd_ratio,
						'z' => $k 
					) );
				}
				if ( !isset( $volume_points[ 8 * $hd_ratio ][ $j ][ $k ] ) ) {
					$volume_points[ 4 * $hd_ratio ][ $j ][ $k ] = new Point( array(
						 'x' => 4 * $hd_ratio,
						'y' => $j + 20 * $hd_ratio,
						'z' => $k 
					) );
				}
			}
		}
		for ( $i = 0; $i < 9 * $hd_ratio; $i++ ) {
			for ( $k = 0; $k < 5 * $hd_ratio; $k++ ) {
				if ( !isset( $volume_points[ $i ][ 0 ][ $k ] ) ) {
					$volume_points[ $i ][ 0 ][ $k ] = new Point( array(
						 'x' => $i,
						'y' => 0 + 20 * $hd_ratio,
						'z' => $k 
					) );
				}
				if ( !isset( $volume_points[ $i ][ 12 * $hd_ratio ][ $k ] ) ) {
					$volume_points[ $i ][ 12 * $hd_ratio ][ $k ] = new Point( array(
						 'x' => $i,
						'y' => 12 * $hd_ratio + 20 * $hd_ratio,
						'z' => $k 
					) );
				}
			}
		}
		for ( $i = 0; $i < 4 * $hd_ratio; $i++ ) {
			for ( $j = 0; $j < 12 * $hd_ratio; $j++ ) {
				$polygons[ 'rightLeg' ][ 'back' ][]  = new Polygon( array(
					 $volume_points[ $i ][ $j ][ 0 ],
					$volume_points[ $i + 1 ][ $j ][ 0 ],
					$volume_points[ $i + 1 ][ $j + 1 ][ 0 ],
					$volume_points[ $i ][ $j + 1 ][ 0 ] 
				), imagecolorat( $img_png, ( 16 * $hd_ratio - 1 ) - $i, 20 * $hd_ratio + $j ) );
				$polygons[ 'rightLeg' ][ 'front' ][] = new Polygon( array(
					 $volume_points[ $i ][ $j ][ 4 * $hd_ratio ],
					$volume_points[ $i + 1 ][ $j ][ 4 * $hd_ratio ],
					$volume_points[ $i + 1 ][ $j + 1 ][ 4 * $hd_ratio ],
					$volume_points[ $i ][ $j + 1 ][ 4 * $hd_ratio ] 
				), imagecolorat( $img_png, 4 * $hd_ratio + $i, 20 * $hd_ratio + $j ) );
			}
		}
		for ( $j = 0; $j < 12 * $hd_ratio; $j++ ) {
			for ( $k = 0; $k < 4 * $hd_ratio; $k++ ) {
				$polygons[ 'rightLeg' ][ 'right' ][] = new Polygon( array(
					 $volume_points[ 0 ][ $j ][ $k ],
					$volume_points[ 0 ][ $j ][ $k + 1 ],
					$volume_points[ 0 ][ $j + 1 ][ $k + 1 ],
					$volume_points[ 0 ][ $j + 1 ][ $k ] 
				), imagecolorat( $img_png, 0 + $k, 20 * $hd_ratio + $j ) );
				$polygons[ 'rightLeg' ][ 'left' ][]  = new Polygon( array(
					 $volume_points[ 4 * $hd_ratio ][ $j ][ $k ],
					$volume_points[ 4 * $hd_ratio ][ $j ][ $k + 1 ],
					$volume_points[ 4 * $hd_ratio ][ $j + 1 ][ $k + 1 ],
					$volume_points[ 4 * $hd_ratio ][ $j + 1 ][ $k ] 
				), imagecolorat( $img_png, ( 12 * $hd_ratio - 1 ) - $k, 20 * $hd_ratio + $j ) );
			}
		}
		for ( $i = 0; $i < 4 * $hd_ratio; $i++ ) {
			for ( $k = 0; $k < 4 * $hd_ratio; $k++ ) {
				$polygons[ 'rightLeg' ][ 'top' ][]    = new Polygon( array(
					 $volume_points[ $i ][ 0 ][ $k ],
					$volume_points[ $i + 1 ][ 0 ][ $k ],
					$volume_points[ $i + 1 ][ 0 ][ $k + 1 ],
					$volume_points[ $i ][ 0 ][ $k + 1 ] 
				), imagecolorat( $img_png, 4 * $hd_ratio + $i, 16 * $hd_ratio + $k ) );
				$polygons[ 'rightLeg' ][ 'bottom' ][] = new Polygon( array(
					 $volume_points[ $i ][ 12 * $hd_ratio ][ $k ],
					$volume_points[ $i + 1 ][ 12 * $hd_ratio ][ $k ],
					$volume_points[ $i + 1 ][ 12 * $hd_ratio ][ $k + 1 ],
					$volume_points[ $i ][ 12 * $hd_ratio ][ $k + 1 ] 
				), imagecolorat( $img_png, 8 * $hd_ratio + $i, ( 20 * $hd_ratio - 1 ) - $k ) );
			}
		}
		// LEFT LEG
		$volume_points = array();
		for ( $i = 0; $i < 9 * $hd_ratio; $i++ ) {
			for ( $j = 0; $j < 13 * $hd_ratio; $j++ ) {
				if ( !isset( $volume_points[ $i ][ $j ][ 0 ] ) ) {
					$volume_points[ $i ][ $j ][ 0 ] = new Point( array(
						 'x' => $i + 4 * $hd_ratio,
						'y' => $j + 20 * $hd_ratio,
						'z' => 0 
					) );
				}
				if ( !isset( $volume_points[ $i ][ $j ][ 4 * $hd_ratio ] ) ) {
					$volume_points[ $i ][ $j ][ 4 * $hd_ratio ] = new Point( array(
						 'x' => $i + 4 * $hd_ratio,
						'y' => $j + 20 * $hd_ratio,
						'z' => 4 * $hd_ratio 
					) );
				}
			}
		}
		for ( $j = 0; $j < 13 * $hd_ratio; $j++ ) {
			for ( $k = 0; $k < 5 * $hd_ratio; $k++ ) {
				if ( !isset( $volume_points[ 0 ][ $j ][ $k ] ) ) {
					$volume_points[ 0 ][ $j ][ $k ] = new Point( array(
						 'x' => 0 + 4 * $hd_ratio,
						'y' => $j + 20 * $hd_ratio,
						'z' => $k 
					) );
				}
				if ( !isset( $volume_points[ 8 * $hd_ratio ][ $j ][ $k ] ) ) {
					$volume_points[ 4 * $hd_ratio ][ $j ][ $k ] = new Point( array(
						 'x' => 4 * $hd_ratio + 4 * $hd_ratio,
						'y' => $j + 20 * $hd_ratio,
						'z' => $k 
					) );
				}
			}
		}
		for ( $i = 0; $i < 9 * $hd_ratio; $i++ ) {
			for ( $k = 0; $k < 5 * $hd_ratio; $k++ ) {
				if ( !isset( $volume_points[ $i ][ 0 ][ $k ] ) ) {
					$volume_points[ $i ][ 0 ][ $k ] = new Point( array(
						 'x' => $i + 4 * $hd_ratio,
						'y' => 0 + 20 * $hd_ratio,
						'z' => $k 
					) );
				}
				if ( !isset( $volume_points[ $i ][ 12 * $hd_ratio ][ $k ] ) ) {
					$volume_points[ $i ][ 12 * $hd_ratio ][ $k ] = new Point( array(
						 'x' => $i + 4 * $hd_ratio,
						'y' => 12 * $hd_ratio + 20 * $hd_ratio,
						'z' => $k 
					) );
				}
			}
		}
		for ( $i = 0; $i < 4 * $hd_ratio; $i++ ) {
			for ( $j = 0; $j < 12 * $hd_ratio; $j++ ) {
				$polygons[ 'leftLeg' ][ 'back' ][]  = new Polygon( array(
					 $volume_points[ $i ][ $j ][ 0 ],
					$volume_points[ $i + 1 ][ $j ][ 0 ],
					$volume_points[ $i + 1 ][ $j + 1 ][ 0 ],
					$volume_points[ $i ][ $j + 1 ][ 0 ] 
				), imagecolorat( $img_png, ( 16 * $hd_ratio - 1 ) - ( ( 4 * $hd_ratio - 1 ) - $i ), 20 * $hd_ratio + $j ) );
				$polygons[ 'leftLeg' ][ 'front' ][] = new Polygon( array(
					 $volume_points[ $i ][ $j ][ 4 * $hd_ratio ],
					$volume_points[ $i + 1 ][ $j ][ 4 * $hd_ratio ],
					$volume_points[ $i + 1 ][ $j + 1 ][ 4 * $hd_ratio ],
					$volume_points[ $i ][ $j + 1 ][ 4 * $hd_ratio ] 
				), imagecolorat( $img_png, 4 * $hd_ratio + ( ( 4 * $hd_ratio - 1 ) - $i ), 20 * $hd_ratio + $j ) );
			}
		}
		for ( $j = 0; $j < 12 * $hd_ratio; $j++ ) {
			for ( $k = 0; $k < 4 * $hd_ratio; $k++ ) {
				$polygons[ 'leftLeg' ][ 'right' ][] = new Polygon( array(
					 $volume_points[ 0 ][ $j ][ $k ],
					$volume_points[ 0 ][ $j ][ $k + 1 ],
					$volume_points[ 0 ][ $j + 1 ][ $k + 1 ],
					$volume_points[ 0 ][ $j + 1 ][ $k ] 
				), imagecolorat( $img_png, 0 + ( ( 4 * $hd_ratio - 1 ) - $k ), 20 * $hd_ratio + $j ) );
				$polygons[ 'leftLeg' ][ 'left' ][]  = new Polygon( array(
					 $volume_points[ 4 * $hd_ratio ][ $j ][ $k ],
					$volume_points[ 4 * $hd_ratio ][ $j ][ $k + 1 ],
					$volume_points[ 4 * $hd_ratio ][ $j + 1 ][ $k + 1 ],
					$volume_points[ 4 * $hd_ratio ][ $j + 1 ][ $k ] 
				), imagecolorat( $img_png, ( 12 * $hd_ratio - 1 ) - ( ( 4 * $hd_ratio - 1 ) - $k ), 20 * $hd_ratio + $j ) );
			}
		}
		for ( $i = 0; $i < 4 * $hd_ratio; $i++ ) {
			for ( $k = 0; $k < 4 * $hd_ratio; $k++ ) {
				$polygons[ 'leftLeg' ][ 'top' ][]    = new Polygon( array(
					 $volume_points[ $i ][ 0 ][ $k ],
					$volume_points[ $i + 1 ][ 0 ][ $k ],
					$volume_points[ $i + 1 ][ 0 ][ $k + 1 ],
					$volume_points[ $i ][ 0 ][ $k + 1 ] 
				), imagecolorat( $img_png, 4 * $hd_ratio + ( ( 4 * $hd_ratio - 1 ) - $i ), 16 * $hd_ratio + $k ) );
				$polygons[ 'leftLeg' ][ 'bottom' ][] = new Polygon( array(
					 $volume_points[ $i ][ 12 * $hd_ratio ][ $k ],
					$volume_points[ $i + 1 ][ 12 * $hd_ratio ][ $k ],
					$volume_points[ $i + 1 ][ 12 * $hd_ratio ][ $k + 1 ],
					$volume_points[ $i ][ 12 * $hd_ratio ][ $k + 1 ] 
				), imagecolorat( $img_png, 8 * $hd_ratio + ( ( 4 * $hd_ratio - 1 ) - $i ), ( 20 * $hd_ratio - 1 ) - $k ) );
			}
		}
	}
	
	// Pre-projection - Rotation of members if required.
	$times[] = array(
		 'Polygon-generation',
		microtime_float() 
	);
	
	foreach ( $polygons[ 'head' ] as $face ) {
		foreach ( $face as $poly ) {
			$poly->preProject( 4, 8, 2, $members_angles[ 'head' ][ 'cos_alpha' ], $members_angles[ 'head' ][ 'sin_alpha' ], $members_angles[ 'head' ][ 'cos_omega' ], $members_angles[ 'head' ][ 'sin_omega' ] );
		}
	}
	
	if ($display_hair) {
		foreach ( $polygons[ 'helmet' ] as $face ) {
			foreach ( $face as $poly ) {
				$poly->preProject( 4, 8, 2, $members_angles[ 'head' ][ 'cos_alpha' ], $members_angles[ 'head' ][ 'sin_alpha' ], $members_angles[ 'head' ][ 'cos_omega' ], $members_angles[ 'head' ][ 'sin_omega' ] );
			}
		}
	}
	
	if (!$head_only) {
		foreach ( $polygons[ 'rightArm' ] as $face ) {
			foreach ( $face as $poly ) {
				$poly->preProject( -2, 8, 2, $members_angles[ 'rightArm' ][ 'cos_alpha' ], $members_angles[ 'rightArm' ][ 'sin_alpha' ], $members_angles[ 'rightArm' ][ 'cos_omega' ], $members_angles[ 'rightArm' ][ 'sin_omega' ] );
			}
		}
		foreach ( $polygons[ 'leftArm' ] as $face ) {
			foreach ( $face as $poly ) {
				$poly->preProject( 10, 8, 2, $members_angles[ 'leftArm' ][ 'cos_alpha' ], $members_angles[ 'leftArm' ][ 'sin_alpha' ], $members_angles[ 'leftArm' ][ 'cos_omega' ], $members_angles[ 'leftArm' ][ 'sin_omega' ] );
			}
		}
		foreach ( $polygons[ 'rightLeg' ] as $face ) {
			foreach ( $face as $poly ) {
				$poly->preProject( 2, 20, ( $members_angles[ 'rightLeg' ][ 'sin_alpha' ] < 0 ? 0 : 4 ), $members_angles[ 'rightLeg' ][ 'cos_alpha' ], $members_angles[ 'rightLeg' ][ 'sin_alpha' ], $members_angles[ 'rightLeg' ][ 'cos_omega' ], $members_angles[ 'rightLeg' ][ 'sin_omega' ] );
			}
		}
		foreach ( $polygons[ 'leftLeg' ] as $face ) {
			foreach ( $face as $poly ) {
				$poly->preProject( 6, 20, ( $members_angles[ 'leftLeg' ][ 'sin_alpha' ] < 0 ? 0 : 4 ), $members_angles[ 'leftLeg' ][ 'cos_alpha' ], $members_angles[ 'leftLeg' ][ 'sin_alpha' ], $members_angles[ 'leftLeg' ][ 'cos_omega' ], $members_angles[ 'leftLeg' ][ 'sin_omega' ] );
			}
		}
	}
	
	// Rotation of the members.
	$times[] = array(
		 'Members-rotation',
		microtime_float() 
	);
	
	foreach ( $polygons as $piece ) {
		foreach ( $piece as $face ) {
			foreach ( $face as $poly ) {
				if ( !$poly->isProjected() ) {
					$poly->project();
				}
			}
		}
	}
	
	$times[] = array(
		 'Projection-plan',
		microtime_float() 
	);
	
	$width   = $maxX - $minX;
	$height  = $maxY - $minY;
	$ratio   = intval( grabGetValue('ratio') );
	if ( $ratio < 2 ) {
		$ratio = 2;
	}
	
	if($aa === true) {
		// double the ration for downscaling later (sort of AA)
		$ratio = $ratio * 2;
	}
	
	if ($seconds_to_cache > 0) {
		$ts = gmdate( "D, d M Y H:i:s", time() + $seconds_to_cache ) . ' GMT';
		header( 'Expires: ' . $ts );
		header( 'Pragma: cache' );
		header( 'Cache-Control: max-age=' . $seconds_to_cache );
	}
	
	if (grabGetValue('format') == 'svg') {
		header( 'Content-Type: image/svg+xml' );
		echo '<?xml version="1.0" standalone="no"?>
			<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"
			"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
			 
			<svg width="100%" height="100%" version="1.1"
			xmlns="http://www.w3.org/2000/svg" viewBox="' . $minX . ' ' . $minY . ' ' . $width . ' ' . $height . '">';
	} else {
		$srcWidth = $ratio * $width + 1;
		$srcHeight = $ratio * $height + 1;
		$realWidth = $srcWidth / 2;
		$realHeight = $srcHeight / 2;
		
		$image = createEmptyCanvas( $srcWidth, $srcHeight);
	}
	
	$display_order = array();
	if ( in_array( 'top', $front_faces ) ) {
		if ( in_array( 'right', $front_faces ) ) {
			$display_order[] = array('leftLeg' => $back_faces);
			$display_order[] = array('leftLeg' => $visible_faces['leftLeg']['front']);
			$display_order[] = array('rightLeg' => $back_faces);
			$display_order[] = array('rightLeg' => $visible_faces['rightLeg']['front']);
			$display_order[] = array('leftArm' => $back_faces);
			$display_order[] = array('leftArm' => $visible_faces['leftArm']['front']);
			$display_order[] = array('torso' => $back_faces);
			$display_order[] = array('torso' => $visible_faces['torso']['front']);
			$display_order[] = array('rightArm' => $back_faces);
			$display_order[] = array('rightArm' => $visible_faces['rightArm']['front']);
		} else {
			$display_order[] = array('rightLeg' => $back_faces);
			$display_order[] = array('rightLeg' => $visible_faces['rightLeg' ]['front']);
			$display_order[] = array('leftLeg' => $back_faces);
			$display_order[] = array('leftLeg' => $visible_faces['leftLeg']['front']);
			$display_order[] = array('rightArm' => $back_faces);
			$display_order[] = array('rightArm' => $visible_faces['rightArm']['front']);
			$display_order[] = array('torso' => $back_faces);
			$display_order[] = array('torso' => $visible_faces['torso']['front']);
			$display_order[] = array('leftArm' => $back_faces);
			$display_order[] = array('leftArm' => $visible_faces['leftArm']['front']);
		}
		
		$display_order[] = array('helmet' => $back_faces);
		$display_order[] = array('head' => $back_faces);
		$display_order[] = array('head' => $visible_faces['head']['front']);
		$display_order[] = array('helmet' => $visible_faces['head']['front']);
	} else {
		$display_order[] = array('helmet' => $back_faces);
		$display_order[] = array('head' => $back_faces);
		$display_order[] = array('head' => $visible_faces['head']['front']);
		$display_order[] = array('helmet' => $visible_faces['head']['front']);
		
		if ( in_array( 'right', $front_faces ) ) {
			$display_order[] = array('leftArm' => $back_faces);
			$display_order[] = array('leftArm' => $visible_faces['leftArm']['front']);
			$display_order[] = array('torso' => $back_faces);
			$display_order[] = array('torso' => $visible_faces['torso']['front']);
			$display_order[] = array('rightArm' => $back_faces);
			$display_order[] = array('rightArm' => $visible_faces['rightArm']['front']);
			$display_order[] = array('leftLeg' => $back_faces);
			$display_order[] = array('leftLeg' => $visible_faces['leftLeg' ]['front']);
			$display_order[] = array('rightLeg' => $back_faces);
			$display_order[] = array('rightLeg' => $visible_faces['rightLeg']['front']);
		} else {
			$display_order[] = array('rightArm' => $back_faces);
			$display_order[] = array('rightArm' => $visible_faces['rightArm']['front']);
			$display_order[] = array('torso' => $back_faces);
			$display_order[] = array('torso' => $visible_faces['torso']['front']);
			$display_order[] = array('leftArm' => $back_faces);
			$display_order[] = array('leftArm' => $visible_faces['leftArm']['front']);
			$display_order[] = array('rightLeg' => $back_faces);
			$display_order[] = array('rightLeg' => $visible_faces['rightLeg']['front']);
			$display_order[] = array('leftLeg' => $back_faces);
			$display_order[] = array('leftLeg' => $visible_faces['leftLeg']['front']);
		}
	}
	
	$times[] = array(
		 'Calculated-display-faces',
		microtime_float() 
	);
	
	foreach ( $display_order as $pieces ) {
		foreach ( $pieces as $piece => $faces ) {
			foreach ( $faces as $face ) {
				foreach ( $polygons[ $piece ][ $face ] as $poly ) {
					if ( grabGetValue('format') == 'svg' )
						echo $poly->getSvgPolygon( 1 );
					else
						$poly->addPngPolygon( $image, $minX, $minY, $ratio );
				}
			}
		}
	}
	
	$times[] = array(
		 'Display-image',
		microtime_float() 
	);
	
	if ( grabGetValue('format') == 'svg' ) {
		echo '</svg>' . "\n";
		for ( $i = 1; $i < count( $times ); $i++ ) {
			echo '<!-- ' . ( $times[ $i ][ 1 ] - $times[ $i - 1 ][ 1 ] ) * 1000 . 'ms : ' . $times[ $i ][ 0 ] . ' -->' . "\n";
		}
		echo '<!-- TOTAL : ' . ( $times[ count( $times ) - 1 ][ 1 ] - $times[ 0 ][ 1 ] ) * 1000 . 'ms -->' . "\n";
	} else {
		
		if($aa === true) {
			// image normal size (sort of AA).
			// resize the image down to it's normal size so it will be smoother
			$destImage = createEmptyCanvas($realWidth, $realHeight);
			
			imagecopyresampled($destImage, $image, 0, 0, 0, 0, $realWidth, $realHeight, $srcWidth, $srcHeight);
			$image = $destImage;
		}
		
		if(grabGetValue('format') == 'base64') {
			// output png;base64
			ob_start();
			imagepng($image);
			$imgData = ob_get_contents();
			ob_end_clean();
			
			header("Content-Type: text/plain");
			echo base64_encode($imgData);
		} else {
			header( 'Content-type: image/png' );
			imagepng( $image );
		}
		
		imagedestroy( $image );
		imagedestroy( $destImage );
		for ( $i = 1; $i < count( $times ); $i++ ) {
			header( 'generation-time-' . $i . '-' . $times[ $i ][ 0 ] . ': ' . ( $times[ $i ][ 1 ] - $times[ $i - 1 ][ 1 ] ) * 1000 . 'ms' );
		}
		header( 'generation-time-' . count( $times ) . '-TOTAL: ' . ( $times[ count( $times ) - 1 ][ 1 ] - $times[ 0 ][ 1 ] ) * 1000 . 'ms' );
	}
	
	/* Point Class
	 *
	 */
	class Point {
		private $_originCoord;
		private $_destCoord = array();
		private $_isProjected = false;
		private $_isPreProjected = false;
		
		function __construct( $originCoord ) {
			if ( is_array( $originCoord ) && count( $originCoord ) == 3 ) {
				$this->_originCoord = array(
					 'x' => ( isset( $originCoord[ 'x' ] ) ? $originCoord[ 'x' ] : 0 ),
					'y' => ( isset( $originCoord[ 'y' ] ) ? $originCoord[ 'y' ] : 0 ),
					'z' => ( isset( $originCoord[ 'z' ] ) ? $originCoord[ 'z' ] : 0 ) 
				);
			} else {
				$this->_originCoord = array(
					 'x' => 0,
					'y' => 0,
					'z' => 0 
				);
			}
		}
		
		function project() {
			global $cos_alpha, $sin_alpha, $cos_omega, $sin_omega;
			global $minX, $maxX, $minY, $maxY;
			// 1, 0, 1, 0
			$x                       = $this->_originCoord[ 'x' ];
			$y                       = $this->_originCoord[ 'y' ];
			$z                       = $this->_originCoord[ 'z' ];
			$this->_destCoord[ 'x' ] = $x * $cos_omega + $z * $sin_omega;
			$this->_destCoord[ 'y' ] = $x * $sin_alpha * $sin_omega + $y * $cos_alpha - $z * $sin_alpha * $cos_omega;
			$this->_destCoord[ 'z' ] = -$x * $cos_alpha * $sin_omega + $y * $sin_alpha + $z * $cos_alpha * $cos_omega;
			$this->_isProjected      = true;
			$minX                    = min( $minX, $this->_destCoord[ 'x' ] );
			$maxX                    = max( $maxX, $this->_destCoord[ 'x' ] );
			$minY                    = min( $minY, $this->_destCoord[ 'y' ] );
			$maxY                    = max( $maxY, $this->_destCoord[ 'y' ] );
		}
		
		function preProject( $dx, $dy, $dz, $cos_alpha, $sin_alpha, $cos_omega, $sin_omega ) {
			if ( !$this->_isPreProjected ) {
				$x                         = $this->_originCoord[ 'x' ] - $dx;
				$y                         = $this->_originCoord[ 'y' ] - $dy;
				$z                         = $this->_originCoord[ 'z' ] - $dz;
				$this->_originCoord[ 'x' ] = $x * $cos_omega + $z * $sin_omega + $dx;
				$this->_originCoord[ 'y' ] = $x * $sin_alpha * $sin_omega + $y * $cos_alpha - $z * $sin_alpha * $cos_omega + $dy;
				$this->_originCoord[ 'z' ] = -$x * $cos_alpha * $sin_omega + $y * $sin_alpha + $z * $cos_alpha * $cos_omega + $dz;
				$this->_isPreProjected     = true;
			}
		}
		
		function getOriginCoord() {
			return $this->_originCoord;
		}
		
		function getDestCoord() {
			return $this->_destCoord;
		}
		
		function getDepth() {
			if ( !$this->_isProjected ) {
				$this->project();
			}
			return $this->_destCoord[ 'z' ];
		}
		
		function isProjected() {
			return $this->_isProjected;
		}
	}
	
	/* Polygon Class
	 *
	 */
	class Polygon {
		private $_dots;
		private $_colour;
		private $_isProjected = false;
		private $_face = 'w';
		private $_faceDepth = 0;
		
		function __construct( $dots, $colour ) {
			$this->_dots   = $dots;
			$this->_colour = $colour;
			$coord_0       = $dots[ 0 ]->getOriginCoord();
			$coord_1       = $dots[ 1 ]->getOriginCoord();
			$coord_2       = $dots[ 2 ]->getOriginCoord();
			if ( $coord_0[ 'x' ] == $coord_1[ 'x' ] && $coord_1[ 'x' ] == $coord_2[ 'x' ] ) {
				$this->_face      = 'x';
				$this->_faceDepth = $coord_0[ 'x' ];
			} else if ( $coord_0[ 'y' ] == $coord_1[ 'y' ] && $coord_1[ 'y' ] == $coord_2[ 'y' ] ) {
				$this->_face      = 'y';
				$this->_faceDepth = $coord_0[ 'y' ];
			} else if ( $coord_0[ 'z' ] == $coord_1[ 'z' ] && $coord_1[ 'z' ] == $coord_2[ 'z' ] ) {
				$this->_face      = 'z';
				$this->_faceDepth = $coord_0[ 'z' ];
			}
		}
		
		function getFace() {
			return $this->_face;
		}
		
		function getFaceDepth() {
			if ( !$this->_isProjected ) {
				$this->project();
			}
			return $this->_faceDepth;
		}
		
		function getSvgPolygon( $ratio ) {
			$points_2d = '';
			$r         = ( $this->_colour >> 16 ) & 0xFF;
			$g         = ( $this->_colour >> 8 ) & 0xFF;
			$b         = $this->_colour & 0xFF;
			$vR        = ( 127 - ( ( $this->_colour & 0x7F000000 ) >> 24 ) ) / 127;
			if ( $vR == 0 )
				return '';
			foreach ( $this->_dots as $dot ) {
				$coord = $dot->getDestCoord();
				$points_2d .= $coord[ 'x' ] * $ratio . ',' . $coord[ 'y' ] * $ratio . ' ';
			}
			$comment = '';
			return $comment . '<polygon points="' . $points_2d . '" style="fill:rgba(' . $r . ',' . $g . ',' . $b . ',' . $vR . ')" />' . "\n";
		}
		
		function addPngPolygon( &$image, $minX, $minY, $ratio ) {
			$points_2d = array();
			$nb_points = 0;
			$r         = ( $this->_colour >> 16 ) & 0xFF;
			$g         = ( $this->_colour >> 8 ) & 0xFF;
			$b         = $this->_colour & 0xFF;
			$vR        = ( 127 - ( ( $this->_colour & 0x7F000000 ) >> 24 ) ) / 127;
			if ( $vR == 0 )
				return;
			$same_plan_x = true;
			$same_plan_y = true;
			foreach ( $this->_dots as $dot ) {
				$coord = $dot->getDestCoord();
				if ( !isset( $coord_x ) )
					$coord_x = $coord[ 'x' ];
				if ( !isset( $coord_y ) )
					$coord_y = $coord[ 'y' ];
				if ( $coord_x != $coord[ 'x' ] )
					$same_plan_x = false;
				if ( $coord_y != $coord[ 'y' ] )
					$same_plan_y = false;
				$points_2d[] = ( $coord[ 'x' ] - $minX ) * $ratio;
				$points_2d[] = ( $coord[ 'y' ] - $minY ) * $ratio;
				$nb_points++;
			}
			if ( !( $same_plan_x || $same_plan_y ) ) {
				$colour = imagecolorallocate( $image, $r, $g, $b );
				imagefilledpolygon( $image, $points_2d, $nb_points, $colour );
			}
		}
		
		function isProjected() {
			return $this->_isProjected;
		}
		
		function project() {
			foreach ( $this->_dots as &$dot ) {
				if ( !$dot->isProjected() ) {
					$dot->project();
				}
			}
			$this->_isProjected = true;
		}
		
		function preProject( $dx, $dy, $dz, $cos_alpha, $sin_alpha, $cos_omega, $sin_omega ) {
			foreach ( $this->_dots as &$dot ) {
				$dot->preProject( $dx, $dy, $dz, $cos_alpha, $sin_alpha, $cos_omega, $sin_omega );
			}
		}
	}
?>