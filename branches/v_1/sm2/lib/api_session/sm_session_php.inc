<?php

	class sm_api_session {
		function sm_api_session() {
			if ($SID) {
				session_id($SID);
			}
			session_start();
		}

		function get_id() {
			return session_id();
		}

		function set($name) {
			global $$name;
			session_register($name);
		}

		function get($name) {
			global $$name;
			return $$name;
		}

		function unset_var($name) {
		}

		function isset_var($name) {
			return session_is_registered($name);
		}

		function destroy() {
			session_destroy();
		}
	}

?>
