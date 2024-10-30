<?php
/**
 * Plugin Name: Last-USERS-Dashboard-Widget
 Description: Widget for Dashboard WP. Last (10) registered USERS list. Написано для себя, чтобы видеть перед глазами информацию о последних 10ти (можно поменять количество) свежезарегистрированых пользователях и их активности. Виджет для консоли в админке. Показывается администраторам или редакторам (shown for role 'admin' or 'editor'). Конечно же, речь о спамерах и ботах.
 Plugin URI: 
 Version: 0.305
 Author: Dmitry S.&#9874; (Ben-Ja)
 Author URI: http://www.net4me.net 
 Donate link: http://yasobe.ru/na/net4me
 Tags: widget, dashboard, last, users, registered, tool, info, admin, last-users-dashboard
 License: GPL2
 License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
function ludw_get_user_info_by_id($udid){
	$user_info = get_userdata($udid);
	$res = '<br>fname: '.$user_info->first_name.'<br>lname: '.$user_info->last_name.'<br>user-roles: '.implode(', ', $user_info->roles)."\n";
	return $res;
}

function ludw_count_user_comments($user_email) {
    global $wpdb;
    $count = $wpdb->get_var('SELECT COUNT(comment_ID) FROM ' . $wpdb->comments. ' WHERE comment_author_email = "'.$user_email.'" AND comment_approved = "1" AND comment_type IN ("comment", "")');
    return $count;
}

function ludw_last_registered_users() { 
		global $wpdb;
		$count_last=10; //скольких юзеров выводить. 
		echo "Да, ты админ или редактор!<br>";
		$users_all = count_users();
		echo 'У нас на сайте <b>'.$users_all['total_users'].'</b> пользователей,';
		foreach($users_all['avail_roles'] as $role => $count){ 
			echo '<br><a href="'.admin_url( 'users.php?role=', 'http' ).$role.'" target="_blank">'.$role.':'.$count.'</a>';} echo ". Вот.<br>\n";
		echo "Посмотрим $count_last свежезарегистрированых и их активность:";
		$lastusers = $wpdb->get_results("SELECT ID, user_login, user_email, user_url, user_registered FROM $wpdb->users ORDER BY ID DESC LIMIT $count_last");
		foreach ($lastusers as $lastuser) {
			echo '<hr> ID: '.$lastuser->ID.' <a href="'.admin_url( 'users.php?s=', 'http' ).$lastuser->user_login.'" target="_blank">'.$lastuser->user_login.'</a>'.'<br>email: '.$lastuser->user_email.'<br>url: '.$lastuser->user_url."\n";
			echo ludw_get_user_info_by_id( $lastuser->ID );
			$uposts_posts='<a href="'.admin_url( 'edit.php?author=', 'http' ).$lastuser->ID.'" target="_blank">Постов: '.count_user_posts( $lastuser->ID ).'</a> ';
			$uposts_pages='<a href="'.admin_url( 'edit.php?post_type=page&author=', 'http' ).$lastuser->ID.'" target="_blank">Страниц: '.count_user_posts( $lastuser->ID , 'page' ).'</a> ';
			$uposts_comm='<a href="'.admin_url( 'edit-comments.php?comment_status=all&s=', 'http' ). $lastuser->user_email .'" target="_blank">Комментариев (по имейл): '.ludw_count_user_comments($lastuser->user_email).'</a>';
			echo "<br>".$uposts_posts."|".$uposts_pages."|".$uposts_comm."\n";
			echo '<br>registered: '.$lastuser->user_registered."<br>\n";
		}
}

// Добавляем виджет
function ludw_add_last_dashboardWidgets(){
	if( current_user_can('editor') || current_user_can('administrator') ) {
		wp_add_dashboard_widget( 'ludw_lastusers-admin-widget', 'looking for spammers...', 'ludw_last_registered_users' );
	}
}
add_action( 'wp_dashboard_setup', 'ludw_add_last_dashboardWidgets' );

//END
?>
