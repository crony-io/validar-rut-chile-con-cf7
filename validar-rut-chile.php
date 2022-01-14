<?php
/**
* Plugin Name: Validar RUT Chile con CF7
* Description: Valida campo de RUT chileno utilizando el plugin Contact Form 7. Este plugin depende de Contact Form 7.
* Author: rafaelcrony
* Version: 1.1
* Author URI: https://www.crony.io
* Text Domain: validar-rut-chile-con-cf7
* License: GPLv3 or later
* License URI: https://www.gnu.org/licenses/gpl-3.0.txt
*/

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

function rutcf7_requerido(){
	$plugin_messages = '';

	 if ( file_exists( WP_PLUGIN_DIR . '/contact-form-7/wp-contact-form-7.php' ) ){
		if(!is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ))	{
			// Activate Contact form 7
			$plugin_messages = __( 'El plugin <strong>Validar RUT Chile con CF7</strong> requiere que el plugin Contact Form 7 esté activado.', 'validar-rut-chile-con-cf7' );
		}
	 }else{
		// Download Contact form 7
		$plugin_messages = __( 'El plugin <strong>Validar RUT Chile con CF7</strong> depende del plugin Contact Form 7 para funcionar, <a href="https://wordpress.org/plugins/contact-form-7/" target="_blank">Descargalo aquí</a>.', 'validar-rut-chile-con-cf7' );
	 }
	if(!empty($plugin_messages)){
		echo '<div class="notice notice-error is-dismissible">';
		echo '<p>'.wp_kses_post($plugin_messages).'</p>';
		echo '</div>';
	}
}
add_action('admin_notices', 'rutcf7_requerido');

if ( file_exists( WP_PLUGIN_DIR . '/contact-form-7/wp-contact-form-7.php' ) ){
	if(is_plugin_active( 'contact-form-7/wp-contact-form-7.php' )){
        if (!function_exists('cf7rut_validation_callback_action')) {
			// Lógica validación
			function cf7rut_validation_callback_action($rut) {
				if(strlen($rut) > 12){ 
					return false; 
				} 
			
				if(strstr($rut, '-') == false){ 
					return false; 
				} 
				$rut = str_replace('.', '', $rut);
				$array_rut_sin_guion = explode('-',$rut); // separamos el la cadena del digito verificador. 
				$rut_sin_guion = $array_rut_sin_guion[0]; // la primera cadena 
				$digito_verificador = $array_rut_sin_guion[1];// el digito despues del guion. 
				if(is_numeric($rut_sin_guion)== false) { 
					return false; 
				} 
				if ($digito_verificador != 'k' and $digito_verificador != 'K'){ 
				    if(is_numeric($digito_verificador)== false){ 
						return false; 
					} 
				} 
				$cantidad = strlen($rut_sin_guion); //8 o 7 elementos 
				for ( $i = 0; $i < $cantidad; $i++)//pasamos el rut sin guion a un vector 
			    { 
					$rut_array[$i] = $rut_sin_guion{$i}; 
			    }   
				$i = ($cantidad-1); 
				$x=$i; 
				for ($ib = 0; $ib < $cantidad; $ib++)// ingresamos los elementos del ventor rut_array en otro vector pero al reves. 
			    { 
			    	$rut_reverse[$ib]= $rut_array[$i]; 
					$rut_reverse[$ib]; 
					$i=$i-1; 
			    } 
				$i=2; 
				$ib=0; 
				$acum=0;  
				do{ 
			    	if( $i > 7 ){ 
				    	$i=2; 
					} 
					$acum = $acum + ($rut_reverse[$ib]*$i); 
					$i++; 
					$ib++; 
				}while( $ib <= $x); 
				$resto = $acum%11; 
				$resultado = 11-$resto; 
				if ($resultado == 11) { $resultado=0; } 
				if ($resultado == 10) { $resultado='k'; } 
				if ($digito_verificador == 'k' or $digito_verificador =='K') { $digito_verificador='k';} 
				if ($resultado == $digito_verificador){ 
				    return true; 
			    }else{ 
				    return false; 
			    } 	
			}
	}
	if (!function_exists('cf7rut_validacion')) {
		function cf7rut_validacion($result, $tag) {
			$result_actual = $result['valid'];

			$type = $tag['basetype'];
			$name = $tag['name'];
			//Si esta vacio y es obligatorio lanzar error invalid_required
			if($type == 'text*' && $_POST[$name] == ''){
					$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );	
			}
			//validaciones
			if($name == 'rut') {

				$cf7rut = sanitize_text_field($_POST['rut']);
				if($cf7rut != '') {
					if(cf7rut_validation_callback_action($cf7rut) == false){
						$result->invalidate( $tag, __( 'RUT Invalido', 'validar-rut-chile-con-cf7' ));					
					}else{
						if($result_actual == false){
							$result['valid'] = false;
						}else{
							$result['valid'] = true;
						}
					}			
				}
			}

			
			return $result;
			
		}
	}

		//add filter para validación text
		add_filter( 'wpcf7_validate_text', 'cf7rut_validacion', 10, 2 );
		add_filter( 'wpcf7_validate_text*', 'cf7rut_validacion', 10, 2 );
	}
}
?>