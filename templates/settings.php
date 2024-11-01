<div class="wrap">
    <h2>VD Likes setting</h2>
    <form method="post" action="options.php"> 
        <?php @settings_fields('vd_like_template-group'); ?>
        <?php @do_settings_fields('vd_like_template-group'); ?>		
        <table class="form-table">  
            <tr valign="top">
                <th scope="row"><label for="_vd_like_user_login">Only registered user can like or unlike</label></th>
                <td>
					<input type="checkbox"  name="_vd_like_user_login"  id="_vd_like_user_login" value="1" <?php checked(1,get_option('_vd_like_user_login')); ?> />
					</td>
            </tr>            
        </table>
        <?php @submit_button(); ?>
    </form>
</div>
