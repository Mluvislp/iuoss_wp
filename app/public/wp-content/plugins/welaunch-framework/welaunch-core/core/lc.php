<?php
/**
 * weLaunch – License Validation (every 2 days)
 * Requires: WP 5.2+
 */

if (!defined('ABSPATH')) exit;

// ---------- Multisite-aware storage ----------
if (!defined('WELAUNCH_LICENSES_OPTION'))  define('WELAUNCH_LICENSES_OPTION',  'welaunch_licenses');
if (!defined('WELAUNCH_LIC_STATUS_OPTION')) define('WELAUNCH_LIC_STATUS_OPTION','welaunch_license_status');
if (!defined('WELAUNCH_LIC_LAST_CHECK'))    define('WELAUNCH_LIC_LAST_CHECK',   'welaunch_license_last_check');
if (!defined('WELAUNCH_LIC_RUNNING_TRANS')) define('WELAUNCH_LIC_RUNNING_TRANS','welaunch_license_check_running');

function wl_get_option(string $key, $default = null) {
    return is_multisite() ? get_site_option($key, $default) : get_option($key, $default);
}
function wl_update_option(string $key, $value, $autoload = false) {
    return is_multisite() ? update_site_option($key, $value) : update_option($key, $value, $autoload);
}
function wl_delete_option(string $key) {
    return is_multisite() ? delete_site_option($key) : delete_option($key);
}

function wl_get_transient(string $key) {
    return is_multisite() ? get_site_transient($key) : get_transient($key);
}
function wl_set_transient(string $key, $value, int $expiration) {
    return is_multisite() ? set_site_transient($key, $value, $expiration) : set_transient($key, $value, $expiration);
}
function wl_delete_transient(string $key) {
    return is_multisite() ? delete_site_transient($key) : delete_transient($key);
}

//
// 0) Helpers: get domain + licenses
//
function welaunch_get_current_domain(): string {
    $host = wp_parse_url(home_url(), PHP_URL_HOST);
    $host = strtolower($host ?: '');
    if (function_exists('idn_to_ascii') && $host !== '') {
        $ascii = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        if ($ascii) $host = $ascii;
    }
    // normalize leading www (server can also normalize)
    $host = preg_replace('~^www\.~i', '', $host);
    return $host;
}

function welaunch_get_licenses(): array {
    // Your framework stores: [ item_slug => license_key, ... ]
    $stored = wl_get_option(WELAUNCH_LICENSES_OPTION, []);
    if (!is_array($stored)) return [];
    // Return both shapes for different callers
    $assoc = $stored;
    $flat  = array_values(array_filter(array_map('trim', is_array($stored) && !wp_is_numeric_array($stored) ? array_values($stored) : $stored)));
    return [
        'assoc' => $assoc,     // [item => key]
        'flat'  => $flat,      // ['KEY1','KEY2',...]
    ];
}


//
// 2) Cron schedule (every 2 days)
//
add_filter('cron_schedules', function ($schedules) {
    if (!isset($schedules['every_2_days'])) {
        $schedules['every_2_days'] = [
            'interval' => 2 * DAY_IN_SECONDS,
            'display'  => __('Every 2 days (weLaunch)', 'welaunch'),
        ];
    }
    return $schedules;
});

register_activation_hook(__FILE__, function () {
    if (!wp_next_scheduled('welaunch_validate_licenses_event')) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'every_2_days', 'welaunch_validate_licenses_event');
    }
});

register_deactivation_hook(__FILE__, function () {
    $ts = wp_next_scheduled('welaunch_validate_licenses_event');
    if ($ts) wp_unschedule_event($ts, 'welaunch_validate_licenses_event');
});

add_action('welaunch_validate_licenses_event', 'welaunch_validate_licenses_cron_handler');

//
// 3) Core: remote validation call
//
function welaunch_validate_licenses_cron_handler(bool $force = false): void {
    // avoid overlapping runs
    if (wl_get_transient(WELAUNCH_LIC_RUNNING_TRANS) && !$force) return;
    wl_set_transient(WELAUNCH_LIC_RUNNING_TRANS, 1, 60);

    $licenses = welaunch_get_licenses()['flat'];
    if (empty($licenses)) {
        // no licenses → clear status and exit
        wl_update_option(WELAUNCH_LIC_STATUS_OPTION, ['ok' => true, 'checked_at' => current_time('mysql'), 'licenses' => []], false);
        wl_update_option(WELAUNCH_LIC_LAST_CHECK, time(), false);
        wl_delete_transient(WELAUNCH_LIC_RUNNING_TRANS);
        return;
    }

    $endpoint = 'https://www.welaunch.io/updates/paddle/validate.php';
    $payload  = [
        'domain'   => welaunch_get_current_domain(),
        'licenses' => $licenses,
        // Optional context the server might find useful:
        'site'     => home_url('/'),
        'wp'       => get_bloginfo('version'),
        'php'      => PHP_VERSION,
        'plugin'   => plugin_basename(__FILE__),
    ];

    $args = [
        'timeout'     => 15,
        'redirection' => 3,
        'blocking'    => true,
        'headers'     => [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'User-Agent'   => 'weLaunch-Licenses/1.0; ' . home_url('/'),
        ],
        'body'        => wp_json_encode($payload),
    ];

    $resp = wp_remote_post($endpoint, $args);

    $result = [
        'ok'         => false,
        'checked_at' => current_time('mysql'),
        'error'      => null,
        'licenses'   => [],
    ];

    if (is_wp_error($resp)) {
        $result['error'] = $resp->get_error_message();
    } else {
        $code = (int) wp_remote_retrieve_response_code($resp);
        $body = wp_remote_retrieve_body($resp);
        if ($code >= 200 && $code < 300 && $body) {
            $json = json_decode($body, true);
            if (is_array($json)) {
                // Expected shape (example):
                // {
                //   "ok": true,
                //   "checked_at": "...",
                //   "licenses": {
                //     "AAAAA-BBBBB-...": {"valid":true,"expired":false,"domain_ok":true,"valid_until":"2026-01-01 00:00:00","message":"OK"}
                //   }
                // }
                $result = array_merge($result, $json);
                $result['checked_at'] = current_time('mysql');
            } else {
                $result['error'] = 'Invalid JSON from server';
            }
        } else {
            $result['error'] = 'HTTP ' . $code;
        }
    }

    wl_update_option(WELAUNCH_LIC_STATUS_OPTION, $result, false);
    wl_update_option(WELAUNCH_LIC_LAST_CHECK, time(), false);
    wl_delete_transient(WELAUNCH_LIC_RUNNING_TRANS);
}

//
// 4) Kick a check on admin load if stale (> 2 days + 4h grace)
//
add_action('admin_init', function () {
    $last = (int) wl_get_option(WELAUNCH_LIC_LAST_CHECK, 0);
    if ($last <= 0 || (time() - $last) > (2 * DAY_IN_SECONDS + 4 * HOUR_IN_SECONDS)) {
        welaunch_validate_licenses_cron_handler(true);
    }
});

//
// 5) Quick helper you can use anywhere in your plugin code
//
function welaunch_is_any_license_valid(?string $domain = null): bool {
    $st = wl_get_option(WELAUNCH_LIC_STATUS_OPTION, []);
    $domain = $domain ?: welaunch_get_current_domain();
    if (empty($st['licenses']) || !is_array($st['licenses'])) return false;
    foreach ($st['licenses'] as $key => $info) {
        $valid     = !empty($info['valid']);
        $expired   = !empty($info['expired']);
        $domain_ok = array_key_exists('domain_ok', (array)$info) ? (bool)$info['domain_ok'] : true;
        if ($valid && !$expired && $domain_ok) return true;
    }
    return false;
}

/**
 * Check a specific license (returns array with keys valid, expired, domain_ok, valid_until, message)
 */
function welaunch_get_license_status(string $license): array {
    $st = wl_get_option(WELAUNCH_LIC_STATUS_OPTION, []);

    if (!empty($st['licenses'][$license]) && is_array($st['licenses'][$license])) {
        return $st['licenses'][$license];
    }
    return ['valid' => false, 'expired' => null, 'domain_ok' => null, 'valid_until' => null, 'message' => 'Unknown'];
}

//
// 6) Admin notice if any license is invalid / expired / domain mismatch
//
add_action('admin_notices', function () {
    if (!current_user_can('manage_options')) return;

    $st = wl_get_option(WELAUNCH_LIC_STATUS_OPTION, []);
    if (empty($st['licenses']) || !is_array($st['licenses'])) return;

    $domain = welaunch_get_current_domain();
    $problems = [];
    foreach ($st['licenses'] as $key => $info) {
        $valid     = !empty($info['valid']);
        $expired   = !empty($info['expired']);
        $domain_ok = array_key_exists('domain_ok', (array)$info) ? (bool)$info['domain_ok'] : true;

        if (!$valid || $expired || !$domain_ok) {
            $msg = !empty($info['message']) ? $info['message'] : (
                $expired ? 'License expired' : (!$domain_ok ? 'Activated on a different domain' : 'Invalid license')
            );
            $vu  = !empty($info['valid_until']) ? ' (valid until: ' . esc_html($info['valid_until']) . ')' : '';
            $problems[] = esc_html($key) . ' — ' . esc_html($msg) . $vu;
        }
    }

    if ($problems) {
        echo '<div class="notice notice-error"><p><strong>weLaunch:</strong> License issue detected for domain <code>' . esc_html($domain) . '</code>:</p><ul style="margin-left:18px">';
        foreach ($problems as $p) echo '<li>' . $p . '</li>';
        echo '</ul><p>You can re-check licenses in <a href="' . esc_url(admin_url('tools.php?page=welaunch-framework')) . '">Tools → weLaunch Licenses</a>.</p></div>';
    }
});
