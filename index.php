<?php
/*
Plugin Name: WP Export Users 2
Plugin URI: http://matthewaprice.com/wp-export-user/
Description: Allows for custom csv user data output.  It allows you to customize the Field Separators and Encapsulators.  It gives you a preview of your data that you can copy and paste into a text file or into any application.
Version: 2.0
Author: Matthew Price
Author URI: http://matthewaprice.com
License: GPL2
*/

$WP_Export_Users = new WP_Export_Users();

class WP_Export_Users {
			
	public function __construct() {
	
		add_action( 'admin_menu', array( &$this, 'registerSettingsPage' ) );
	
	}
	
	public function registerSettingsPage() {
	
		add_users_page(__('WP Export Users','WP Export Users'), __('WP Export Users','WP Export Users'), 'manage_options', 'wp-export-users', array( &$this, 'WPExportUsersSettingsPage' ), '', '');
	
	}

	public function WPExportUsersSettingsPage() {

		?>
		<div class="wrap">
			<h2>WP Export Users</h2>
			<form method="post" action="<?php echo plugins_url(); ?>/wp-export-users/download.php">			
			<div id="message" class="updated fade" style="display: none;" >
				<p>Your file is downloading.  Download times will vary based on the size of your user base.</p>			
			</div>			
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?php $this->Label( 'user_login', 'User Login' ); ?></th>
						<td><?php $this->InputField( 'checkbox', 'user_login' ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php $this->Label( 'first_name', 'First Name' ); ?></th>
						<td><?php $this->InputField( 'checkbox', 'first_name' ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php $this->Label( 'last_name', 'Last Name' ); ?></th>
						<td><?php $this->InputField( 'checkbox', 'last_name' ); ?></td>
					</tr>	
					<tr>
						<th scope="row"><?php $this->Label( 'user_email', 'Email' ); ?></th>
						<td><?php $this->InputField( 'checkbox', 'user_email' ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php $this->Label( 'user_pass', 'Password (Encrypted)' ); ?></th>
						<td><?php $this->InputField( 'checkbox', 'user_pass' ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php $this->Label( 'user_url', 'User Url' ); ?></th>
						<td><?php $this->InputField( 'checkbox', 'user_url' ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php $this->Label( 'display_name', 'Display Name' ); ?></th>
						<td><?php $this->InputField( 'checkbox', 'display_name' ); ?></td>
					</tr>					
					<tr>
						<th scope="row"><?php $this->Label( 'user_role', 'User Role' ); ?></th>
						<td><?php $this->InputField( 'select', 'user_role', get_option('wp_user_roles') ); ?></td>
					</tr>	
<!--
					<tr>
						<th scope="row"><?php $this->Label( 'encapsulator', 'Field Encapsulator' ); ?></th>
						<td><?php $this->InputField( 'select', 'encapsulator' ); ?></td>
					</tr>																																									<tr>
						<th scope="row"><?php $this->Label( 'separator', 'Field Separator' ); ?></th>
						<td><?php $this->InputField( 'select', 'separator' ); ?></td>
					</tr>	
-->					
					<tr>
						<th scope="row"><?php $this->Label( 'headers', 'Do you want field headers?' ); ?></th>
						<td><?php $this->InputField( 'checkbox', 'headers' ); ?></td>
					</tr>
				</tbody>
			</table>
			<p class="submit">
				<?php wp_nonce_field( 'wp-export-users-settings' ); ?>	
				<input type="hidden" name="wp_location" value="<?php echo ABSPATH; ?>">	
				<script language="javascript">
				function expandCollapse() {
					for (var i=0; i<expandCollapse.arguments.length; i++) {
						var element = document.getElementById(expandCollapse.arguments[i]);
						element.style.display = (element.style.display == "none") ? "block" : "none";
					}
				}
				</script>	
				<input type="submit" name="submit" id="submit" class="button-primary" value="Output Data" onclick="javascript: expandCollapse('message');"> 
			</p>
		</div>
		<?php
		
	}
	
	private function Label( $name, $label ) {
		
		?><label for="<?php echo $name; ?>"><?php echo $label; ?></label><?php
	
	}
	
	private function InputField( $type, $name, $data = array() ) {
	
		switch ( $type ) :
			
			case 'checkbox' :
				?><input type="checkbox" name="<?php echo $name; ?>" id="<?php echo $name; ?>"><?php
				break;
			case 'select' :
				switch ( $name ) :
					case 'user_role' :
						?>
						<select name="<?php echo $name; ?>">
						<option value="">All Roles</option>
						<?php
						foreach ( $data as $d ) :
							echo "<option value=\"" . $d['name'] . "\">" . $d['name'] . "</option>";
						endforeach;
						?>
						</select>					
						<?php
						break;	
					case 'encapsulator' :
					case 'separator' 	:
						?>
							<select name="<?php echo $name; ?>">
								<option value="comma">Comma (,)</option>
								<option value="semicolon">Semi-Colon (;)</option>
								<option value="pipe">Pipe (|)</option>
								<option value="newline">Line Break</option>
								<option value="squote">Single Quote (')</option>
								<option value="dquote">Double Quote (")</option>
								<option value="none">None</option>
							</select>
						<?php						
						break;							
				endswitch;
				break;
		
		endswitch;
	
	}

}

class WP_Export_Users_Download {
  
	public function getUserData( $options ) {
		$wp_user_search = new WP_User_Query( array( 'fields' => 'all_with_meta', 'role' => $options['user_role'] ) );
		$users = $wp_user_search->get_results();
		$fields = $this->userExportFields();
		
		$i = 0;
		foreach ( $users as $u ) :
/* 			print_r($u); */
			foreach ( $fields as $field ) :
				if ( array_key_exists( $field, $options ) ) :
					switch ( $field ) :
						case 'user_login' 	:
						case 'first_name' 	:
						case 'last_name' 	:
						case 'user_email'	:
						case 'display_name'	:
						case 'user_pass'	:
						case 'user_url'	:
							$user_array[$i][$field] = $u->{$field};
							break;
						case 'user_role' 	:
							$user_array[$i][$field] = $u->roles[0];
							break;
					endswitch;
				endif;
			endforeach;
		$i++;	
		endforeach;
		
		return $user_array;
	
	}
	
	public function outputData( $user_data ) {				

		$h = '';
		if ( $_POST['headers'] ) :
			$post_vars = $_POST;
			unset( $post_vars['_wpnonce'] );
			unset( $post_vars['_wp_http_referer'] );
			unset( $post_vars['submit'] );						
/*
			unset( $post_vars['encapsulator'] );						
			unset( $post_vars['separator'] );						
*/
			unset( $post_vars['headers'] );
			unset( $post_vars['wp_location'] );
			$keys = array_keys( $post_vars );
			$count = count( $keys );
			$i = 1;
			foreach ( $keys as $key => $value ) :
				$h[] = $value;		
			$i++;
			endforeach;		
			$this->echocsv( $h );				
		endif;
				
		$o = '';
		foreach ( $user_data as $key => $value ) :
			$count = count( $value );
			$i = 1;
			$o = array();
			foreach ( $value as $k => $v ) :
				$o[] = $v;
			$i++;	
			endforeach;
			$this->echocsv( $o );			
		endforeach;
		
	}

	private function userExportFields() {
		
		$fields = array(
			'user_login',
			'first_name',
			'last_name',
			'user_email',
			'user_pass',
			'user_url',
			'display_name',
			'user_role',
			'encapsulator',
			'separator',
			'headers'
		);	
		return $fields;
	
	}

	private function echocsv( $fields ) {
	    $separator = '';
	    foreach ( $fields as $field )
	    {
	      if ( preg_match( '/\\r|\\n|,|"/', $field ) )
	      {
	        $field = '"' . str_replace( '"', '""', $field ) . '"';
	      }
	      echo $separator . $field;
	      $separator = ',';
	    }
	    echo "\r\n";
	}		

}
