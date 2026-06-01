<?php
/**
 * Plugin Navbar controller.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage controllers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="mo_ldap_local_page_body">
	<div class="mo_ldap_local_nav_container">
		<div><a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'default' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect mo_ldap_local_normal_nav_icons <?php echo strcmp( $active_tab, 'default' ) === 0 ? 'mo_ldap_local_active_tab' : ''; ?>">
			<span>
			<svg width="24" height="24" viewBox="0 0 24 24" fill="<?php echo strcmp( $active_tab, 'default' ) === 0 ? '#087ae2' : '#000000'; ?>">
				<path d="M20.2509 12.5696V11.4221L21.6909 10.1621C21.9564 9.92811 22.1306 9.60784 22.1827 9.25787C22.2349 8.9079 22.1616 8.55076 21.9759 8.24957L20.2059 5.24957C20.0744 5.02177 19.8853 4.83256 19.6575 4.70092C19.4298 4.56928 19.1715 4.49984 18.9084 4.49957C18.7454 4.49832 18.5833 4.52366 18.4284 4.57457L16.6059 5.18957C16.2913 4.98048 15.963 4.79256 15.6234 4.62707L15.2409 2.73707C15.1723 2.39178 14.9845 2.0816 14.7103 1.86085C14.436 1.6401 14.0929 1.52283 13.7409 1.52957H10.2309C9.87895 1.52283 9.53581 1.6401 9.26158 1.86085C8.98735 2.0816 8.79951 2.39178 8.73092 2.73707L8.34842 4.62707C8.00637 4.79252 7.67565 4.98043 7.35842 5.18957L5.57342 4.54457C5.41689 4.50379 5.2548 4.48859 5.09342 4.49957C4.83038 4.49984 4.57203 4.56928 4.34429 4.70092C4.11656 4.83256 3.92744 5.02177 3.79592 5.24957L2.02592 8.24957C1.85086 8.55031 1.78581 8.90257 1.84191 9.246C1.89801 9.58943 2.07176 9.90267 2.33342 10.1321L3.75092 11.4296V12.5771L2.33342 13.8371C2.06437 14.0681 1.88586 14.3869 1.82957 14.737C1.77328 15.0871 1.84284 15.4459 2.02592 15.7496L3.79592 18.7496C3.92744 18.9774 4.11656 19.1666 4.34429 19.2982C4.57203 19.4299 4.83038 19.4993 5.09342 19.4996C5.25643 19.5008 5.41856 19.4755 5.57342 19.4246L7.39592 18.8096C7.71058 19.0187 8.0388 19.2066 8.37842 19.3721L8.76092 21.2621C8.82951 21.6074 9.01735 21.9175 9.29158 22.1383C9.56581 22.3591 9.90895 22.4763 10.2609 22.4696H13.8009C14.1529 22.4763 14.496 22.3591 14.7703 22.1383C15.0445 21.9175 15.2323 21.6074 15.3009 21.2621L15.6834 19.3721C16.0255 19.2066 16.3562 19.0187 16.6734 18.8096L18.4884 19.4246C18.6433 19.4755 18.8054 19.5008 18.9684 19.4996C19.2315 19.4993 19.4898 19.4299 19.7175 19.2982C19.9453 19.1666 20.1344 18.9774 20.2659 18.7496L21.9759 15.7496C22.151 15.4488 22.216 15.0966 22.1599 14.7532C22.1038 14.4097 21.9301 14.0965 21.6684 13.8671L20.2509 12.5696ZM18.9084 17.9996L16.3359 17.1296C15.7337 17.6397 15.0455 18.0384 14.3034 18.3071L13.7709 20.9996H10.2309L9.69842 18.3371C8.96224 18.0608 8.27771 17.6627 7.67342 17.1596L5.09342 17.9996L3.32342 14.9996L5.36342 13.1996C5.22474 12.4232 5.22474 11.6284 5.36342 10.8521L3.32342 8.99957L5.09342 5.99957L7.66592 6.86957C8.26812 6.35949 8.95637 5.96076 9.69842 5.69207L10.2309 2.99957H13.7709L14.3034 5.66207C15.0396 5.93839 15.7241 6.33643 16.3284 6.83957L18.9084 5.99957L20.6784 8.99957L18.6384 10.7996C18.7771 11.5759 18.7771 12.3707 18.6384 13.1471L20.6784 14.9996L18.9084 17.9996Z"/>
				<path d="M12 16.5C11.11 16.5 10.24 16.2361 9.49994 15.7416C8.75991 15.2471 8.18314 14.5443 7.84254 13.7221C7.50195 12.8998 7.41283 11.995 7.58647 11.1221C7.7601 10.2492 8.18869 9.44736 8.81802 8.81802C9.44736 8.18869 10.2492 7.7601 11.1221 7.58647C11.995 7.41283 12.8998 7.50195 13.7221 7.84254C14.5443 8.18314 15.2471 8.75991 15.7416 9.49994C16.2361 10.24 16.5 11.11 16.5 12C16.506 12.5926 16.3937 13.1805 16.1697 13.7292C15.9457 14.2779 15.6145 14.7763 15.1954 15.1954C14.7763 15.6145 14.2779 15.9457 13.7292 16.1697C13.1805 16.3937 12.5926 16.506 12 16.5ZM12 9C11.6035 8.99077 11.2093 9.06205 10.8411 9.20954C10.473 9.35704 10.1386 9.57768 9.85812 9.85812C9.57768 10.1386 9.35704 10.473 9.20954 10.8411C9.06205 11.2093 8.99077 11.6035 9 12C8.99077 12.3965 9.06205 12.7907 9.20954 13.1589C9.35704 13.527 9.57768 13.8615 9.85812 14.1419C10.1386 14.4223 10.473 14.643 10.8411 14.7905C11.2093 14.938 11.6035 15.0092 12 15C12.3965 15.0092 12.7907 14.938 13.1589 14.7905C13.527 14.643 13.8615 14.4223 14.1419 14.1419C14.4223 13.8615 14.643 13.527 14.7905 13.1589C14.938 12.7907 15.0092 12.3965 15 12C15.0092 11.6035 14.938 11.2093 14.7905 10.8411C14.643 10.473 14.4223 10.1386 14.1419 9.85812C13.8615 9.57768 13.527 9.35704 13.1589 9.20954C12.7907 9.06205 12.3965 8.99077 12 9Z"/>
			</svg>
			</span> Configuration
		</a></div>
		<div><a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'import-export' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect mo_ldap_local_normal_nav_icons <?php echo strcmp( $active_tab, 'import-export' ) === 0 ? 'mo_ldap_local_active_tab' : ''; ?>">
			<span>
				<svg width="25" height="30" viewBox="0 0 34 24" fill="<?php echo strcmp( $active_tab, 'import-export' ) === 0 ? '#087ae2' : '#000000'; ?>" >
					<path d="M4.2011 16.0169L4.16676 6.00858L2.50011 6.0143L2.53444 16.0226L0.034459 16.0311L3.37918 19.3447L6.70109 16.0083L4.2011 16.0169Z" />
					<path d="M29.5009 9.32519L29.5027 19.3335L31.1694 19.3332L31.1675 9.32489L33.6675 9.32443L30.3336 6.00004L27.0009 9.32565L29.5009 9.32519Z" />
					<path d="M25.0314 7.71938L19.7814 2.46938C19.7117 2.39975 19.6289 2.34454 19.5379 2.3069C19.4468 2.26926 19.3493 2.24992 19.2507 2.25H10.2507C9.85291 2.25 9.47138 2.40804 9.19007 2.68934C8.90877 2.97064 8.75073 3.35218 8.75073 3.75V20.25C8.75073 20.6478 8.90877 21.0294 9.19007 21.3107C9.47138 21.592 9.85291 21.75 10.2507 21.75H23.7507C24.1486 21.75 24.5301 21.592 24.8114 21.3107C25.0927 21.0294 25.2507 20.6478 25.2507 20.25V8.25C25.2508 8.15148 25.2315 8.05391 25.1938 7.96286C25.1562 7.87182 25.101 7.78908 25.0314 7.71938ZM20.0007 4.81031L22.6904 7.5H20.0007V4.81031ZM23.7507 20.25H10.2507V3.75H18.5007V8.25C18.5007 8.44891 18.5797 8.63968 18.7204 8.78033C18.8611 8.92098 19.0518 9 19.2507 9H23.7507V20.25Z"/>
					<rect x="12.0007" y="9" width="5" height="1" rx="0.5" />
					<rect x="12.0007" y="11" width="6" height="1" rx="0.5" />
					<rect x="12.0007" y="13" width="6" height="1" rx="0.5" />
					<rect x="12.0007" y="15" width="6" height="1" rx="0.5" />
				</svg>
			</span> Import/Export Configuration
		</a></div>
		<div style="border-bottom:1px solid grey"><a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'users-report' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect mo_ldap_local_normal_nav_icons <?php echo strcmp( $active_tab, 'users-report' ) === 0 ? 'mo_ldap_local_active_tab' : ''; ?>">
			<span>
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="<?php echo strcmp( $active_tab, 'users-report' ) === 0 ? '#087ae2' : '#000000'; ?>">
					<path d="M21 11C21 16.55 17.16 21.74 12 23C6.84 21.74 3 16.55 3 11V5L12 1L21 5V11ZM12 21C15.75 20 19 15.54 19 11.22V6.3L12 3.18L5 6.3V11.22C5 15.54 8.25 20 12 21ZM10 17L6 13L7.41 11.59L10 14.17L16.59 7.58L18 9"/>
				</svg>
			</span> Authentication Report
		</a></div>
		<div class="mo_ldap_local_nav_premium_section mo_ldap_local_column_flex_container">
			<div class="mo_ldap_local_premium_title"><a class="mo_ldap_local_unset_link_affect" href="https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites?utm_source=ldap%20intranet%20plugin&utm_medium=ldap%20free%20plugin&utm_campaign=ldap%20intranet%20free%20plugin" target="_blank">Premium</a></div>
			<div><a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'advance-sync' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect mo_ldap_local_normal_nav_icons <?php echo strcmp( $active_tab, 'advance-sync' ) === 0 ? 'mo_ldap_local_active_tab' : ''; ?>">
				<span>
				<svg width="24" height="24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M22.8217 18.5301C30.3824 11.9787 40.055 8.37866 50.0593 8.39262C73.0718 8.39262 91.7259 27.0468 91.7259 50.0593H83.3926C83.3931 43.5244 81.4728 37.1335 77.8704 31.6812C74.2679 26.2289 69.1423 21.9558 63.1308 19.3931C57.1194 16.8305 50.4872 16.0914 44.0591 17.2677C37.6309 18.444 31.6903 21.4839 26.9759 26.0093L22.8217 18.5301ZM77.2968 81.5885C69.7361 88.14 60.0635 91.74 50.0593 91.726C27.0468 91.726 8.39258 73.0719 8.39258 50.0593H16.7259C16.7254 56.5942 18.6457 62.9852 22.2481 68.4375C25.8506 73.8897 30.9762 78.1629 36.9877 80.7255C42.9991 83.2882 49.6313 84.0273 56.0594 82.851C62.4876 81.6746 68.4282 78.6348 73.1426 74.1094L77.2968 81.5885Z"  fill="<?php echo strcmp( $active_tab, 'advance-sync' ) === 0 ? '#087ae2' : '#000000'; ?>"/>
					<path d="M74 50L87 74V50H74Z" fill="<?php echo strcmp( $active_tab, 'advance-sync' ) === 0 ? '#087ae2' : '#000000'; ?>"/>
					<path d="M100 50L87 74V50H100Z" fill="<?php echo strcmp( $active_tab, 'advance-sync' ) === 0 ? '#087ae2' : '#000000'; ?>"/>
					<path d="M0 51L13 27V51H0Z" fill="<?php echo strcmp( $active_tab, 'advance-sync' ) === 0 ? '#087ae2' : '#000000'; ?>"/>
					<path d="M26 51L13 27V51H26Z"  fill="<?php echo strcmp( $active_tab, 'advance-sync' ) === 0 ? '#087ae2' : '#000000'; ?>"/>
				</svg>
				</span>	Advance Sync
			</a></div>
			<div><a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'multiple-directories' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect mo_ldap_local_normal_nav_icons <?php echo strcmp( $active_tab, 'multiple-directories' ) === 0 ? 'mo_ldap_local_active_tab' : ''; ?>">
				<span>
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
					<path d="M21.6008 8.80039H2.40078M21.6008 8.80039C22.0251 8.80039 22.4321 8.63182 22.7322 8.33176C23.0322 8.0317 23.2008 7.62474 23.2008 7.20039V4.00039C23.2008 3.57604 23.0322 3.16908 22.7322 2.86902C22.4321 2.56896 22.0251 2.40039 21.6008 2.40039H2.40078C1.97643 2.40039 1.56947 2.56896 1.26941 2.86902C0.969352 3.16908 0.800781 3.57604 0.800781 4.00039V7.20039C0.800781 7.62474 0.969352 8.0317 1.26941 8.33176C1.56947 8.63182 1.97643 8.80039 2.40078 8.80039M21.6008 8.80039C22.0251 8.80039 22.4321 8.96896 22.7322 9.26902C23.0322 9.56908 23.2008 9.97604 23.2008 10.4004V13.6004C23.2008 14.0247 23.0322 14.4317 22.7322 14.7318C22.4321 15.0318 22.0251 15.2004 21.6008 15.2004M2.40078 8.80039C1.97643 8.80039 1.56947 8.96896 1.26941 9.26902C0.969352 9.56908 0.800781 9.97604 0.800781 10.4004V13.6004C0.800781 14.0247 0.969352 14.4317 1.26941 14.7318C1.56947 15.0318 1.97643 15.2004 2.40078 15.2004M21.6008 15.2004H2.40078M21.6008 15.2004C22.0251 15.2004 22.4321 15.369 22.7322 15.669C23.0322 15.9691 23.2008 16.376 23.2008 16.8004V20.0004C23.2008 20.4247 23.0322 20.8317 22.7322 21.1318C22.4321 21.4318 22.0251 21.6004 21.6008 21.6004H2.40078C1.97643 21.6004 1.56947 21.4318 1.26941 21.1318C0.969352 20.8317 0.800781 20.4247 0.800781 20.0004V16.8004C0.800781 16.376 0.969352 15.9691 1.26941 15.669C1.56947 15.369 1.97643 15.2004 2.40078 15.2004M3.20078 5.60039H8.00078M3.20078 12.0004H8.00078M3.20078 18.4004H8.00078" stroke="<?php echo strcmp( $active_tab, 'multiple-directories' ) === 0 ? '#087ae2' : '#000000'; ?>"/>
				</svg>
				</span> Multiple Directories
			</a></div>
			<div><a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'addons' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect mo_ldap_local_normal_nav_icons <?php echo strcmp( $active_tab, 'addons' ) === 0 ? 'mo_ldap_local_active_tab' : ''; ?>">
			<span>
			<svg width="24" height="24" viewBox="0 0 24 24" fill="<?php echo strcmp( $active_tab, 'addons' ) === 0 ? '#087ae2' : '#000000'; ?>">
				<path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z"/>
			</svg>
			</span> Add-ons</a></div>
		</div>
		<div class="mo_ldap_local_other_products_nav_section">
			<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'other-products' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect mo_ldap_local_other_product_nav_box">
				<div>
					<img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'basket.svg' ); ?>" height="40px" width="40px">
				</div>
			Our Other products
			</a>
		</div>
	</div>
	<div class="mo_ldap_local_tab_container">
		<?php
		if ( strcmp( $active_tab, 'default' ) === 0 ) {
			require_once MO_LDAP_LOCAL_CONTROLLERS . 'mo-ldap-local-configuration-controller.php';
		} elseif ( strcmp( $active_tab, 'users-report' ) === 0 ) {
			require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-user-reports.php';
		} elseif ( strcmp( $active_tab, 'import-export' ) === 0 ) {
			require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-import-export-page.php';
		} elseif ( strcmp( $active_tab, 'multiple-directories' ) === 0 ) {
			require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-multiple-directories-page.php';
		} elseif ( strcmp( $active_tab, 'advance-sync' ) === 0 ) {
			require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-advance-sync-page.php';
		} elseif ( strcmp( $active_tab, 'addons' ) === 0 ) {
			require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-addons-page.php';
		} elseif ( strcmp( $active_tab, 'other-products' ) === 0 ) {
			require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-other-products-page.php';
		}
		?>
	</div>


</div>
