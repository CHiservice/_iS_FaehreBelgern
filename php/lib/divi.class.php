<?php

class iS_FaehreBelgern_Divi {
	public function __construct() {
		$this->register_divi_module();
	}

	public function register_divi_module() {
		function DS_iS_FaehreBelgern_Divi_Modules(){
			if(class_exists("ET_Builder_Module")) {
				$config  = iS_FaehreBelgern_Config::get_instance();
				$dir     = STYLESHEETDIR."/".$config->get("modulName")."/divi/";
				$modules = array_diff(scandir($dir), array('..', '.'));
				foreach($modules AS $module) {
					include($dir.$module);
				}
			}
		}

		function Prep_DS_iS_FaehreBelgern_Divi_Modules() {
			global $pagenow;

			$is_admin                     = is_admin();
			$action_hook                  = $is_admin ? "wp_loaded" : "wp";
			$required_admin_pages         = array("edit.php", "post.php", "post-new.php", "admin.php", "customize.php", "edit-tags.php", "admin-ajax.php", "export.php"); // list of admin pages where we need to load builder files
			$specific_filter_pages        = array("edit.php", "admin.php", "edit-tags.php" );
			$is_edit_library_page         = "edit.php" === $pagenow && isset( $_GET["post_type"] ) && "et_pb_layout" === $_GET["post_type"];
			$is_role_editor_page          = "admin.php" === $pagenow && isset( $_GET["page"] ) && "et_divi_role_editor" === $_GET["page"];
			$is_import_page               = "admin.php" === $pagenow && isset( $_GET["import"] ) && "wordpress" === $_GET["import"];
			$is_edit_layout_category_page = "edit-tags.php" === $pagenow && isset( $_GET["taxonomy"] ) && "layout_category" === $_GET["taxonomy"];

			if (!$is_admin || ($is_admin && in_array($pagenow, $required_admin_pages) && (!in_array( $pagenow, $specific_filter_pages) || $is_edit_library_page || $is_role_editor_page || $is_edit_layout_category_page || $is_import_page))) {
				add_action($action_hook, "DS_iS_FaehreBelgern_Divi_Modules", 9999);
			}
		}
		Prep_DS_iS_FaehreBelgern_Divi_Modules();
	}
}