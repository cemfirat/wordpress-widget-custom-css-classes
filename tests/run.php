<?php
declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

$GLOBALS['ccf_test_actions'] = [];
$GLOBALS['ccf_test_filters'] = [];
$GLOBALS['ccf_test_sidebars'] = [];
$GLOBALS['ccf_test_fallback_enabled'] = true;

function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
    $GLOBALS['ccf_test_actions'][] = [$hook, $callback, $priority, $accepted_args];
    return true;
}

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
    $GLOBALS['ccf_test_filters'][] = [$hook, $callback, $priority, $accepted_args];
    return true;
}

function apply_filters($hook, $value, ...$args) {
    if ('ccf_widget_css_classes_enable_html_fallback' === $hook) {
        return $GLOBALS['ccf_test_fallback_enabled'];
    }
    return $value;
}

function plugin_basename($file) {
    return 'wordpress-widget-custom-css-classes/widget-css-classes.php';
}

function wp_unslash($value) {
    return $value;
}

function sanitize_text_field($value) {
    return trim(strip_tags((string) $value));
}

function esc_attr($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function esc_attr__($value, $domain = null) {
    return esc_attr($value);
}

function esc_html_e($value, $domain = null) {
    echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function wp_doing_ajax() {
    return false;
}

function wp_is_json_request() {
    return false;
}

function is_admin() {
    return false;
}

function is_feed() {
    return false;
}

function is_trackback() {
    return false;
}

function wp_get_sidebars_widgets() {
    return $GLOBALS['ccf_test_sidebars'];
}

function absint($value) {
    return abs((int) $value);
}

class WP_Widget {
    protected array $settings = [];

    public function __construct(array $settings = []) {
        $this->settings = $settings;
    }

    public function get_settings() {
        return $this->settings;
    }
}

final class CCF_Test_Widget extends WP_Widget {}

final class WP_HTML_Tag_Processor {
    private string $html;
    private array $tags = [];
    private int $index = -1;
    private array $classes = [];

    public function __construct(string $html) {
        $this->html = $html;
        preg_match_all(
            '/<([a-z][a-z0-9:-]*)(\\s[^>]*)?>/i',
            $html,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        foreach ($matches[0] as $i => $full) {
            $this->tags[] = [
                'full' => $full[0],
                'offset' => $full[1],
                'name' => $matches[1][$i][0],
                'attributes' => $matches[2][$i][0] ?? '',
            ];
        }
    }

    public function next_tag() {
        $this->index++;
        return isset($this->tags[$this->index]);
    }

    public function get_attribute($name) {
        if (!isset($this->tags[$this->index])) {
            return null;
        }
        $attributes = $this->tags[$this->index]['attributes'];
        if (preg_match(
            '/\\s' . preg_quote((string) $name, '/') . '\\s*=\\s*(["\\\'])(.*?)\\1/i',
            $attributes,
            $match
        )) {
            return $match[2];
        }
        return null;
    }

    public function add_class($class_name) {
        if (!isset($this->tags[$this->index])) {
            return false;
        }
        $this->classes[$this->index][] = (string) $class_name;
        return true;
    }

    public function get_updated_html() {
        $html = $this->html;
        for ($i = count($this->tags) - 1; $i >= 0; $i--) {
            if (empty($this->classes[$i])) {
                continue;
            }

            $tag = $this->tags[$i];
            $attributes = $tag['attributes'];
            $extra = implode(' ', array_values(array_unique($this->classes[$i])));

            if (preg_match('/\\sclass\\s*=\\s*(["\\\'])(.*?)\\1/i', $attributes)) {
                $attributes = preg_replace_callback(
                    '/\\sclass\\s*=\\s*(["\\\'])(.*?)\\1/i',
                    static function ($matches) use ($extra) {
                        return ' class=' . $matches[1]
                            . trim($matches[2] . ' ' . $extra)
                            . $matches[1];
                    },
                    $attributes,
                    1
                );
            } else {
                $attributes .= ' class="' . $extra . '"';
            }

            $replacement = '<' . $tag['name'] . $attributes . '>';
            $html = substr_replace(
                $html,
                $replacement,
                $tag['offset'],
                strlen($tag['full'])
            );
        }
        return $html;
    }
}

require dirname(__DIR__) . '/widget-css-classes.php';

function ccf_test_assert($condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function ccf_test_plugin(): CCF_Widget_CSS_Classes {
    return new CCF_Widget_CSS_Classes();
}

function ccf_test_configure_widget(array $instance): void {
    global $wp_registered_widgets;

    $widget = new CCF_Test_Widget([
        2 => $instance,
    ]);

    $wp_registered_widgets = [
        'text-2' => [
            'callback' => [$widget, 'widget'],
            'params' => [
                ['number' => 2],
            ],
        ],
    ];

    $GLOBALS['ccf_test_sidebars'] = [
        'sidebar-1' => ['text-2'],
        'wp_inactive_widgets' => [],
        'array_version' => 3,
    ];
}

$hooks = array_column($GLOBALS['ccf_test_filters'], 0);
ccf_test_assert(
    !in_array('use_widgets_block_editor', $hooks, true),
    'plugin must not globally disable the Widgets Block Editor'
);

$plugin = ccf_test_plugin();
$method = new ReflectionMethod(CCF_Widget_CSS_Classes::class, 'sanitize_class_list');
$method->setAccessible(true);
$clean = $method->invoke($plugin, ' alpha  alpha uk-margin-large bad" x=y ');
ccf_test_assert(
    'alpha uk-margin-large bad xy' === $clean,
    'class sanitization should de-duplicate tokens and remove unsafe delimiter characters'
);

ccf_test_configure_widget([
    '_ccf_css_classes' => 'alpha beta',
]);
$params = [[
    'widget_id' => 'text-2',
    'before_widget' => '<section id="text-2" class="widget">',
]];
$filtered = $plugin->filter_sidebar_params($params);
ccf_test_assert(
    false !== strpos($filtered[0]['before_widget'], 'widget alpha beta'),
    'standard before_widget output should receive configured classes'
);

ccf_test_configure_widget([]);
$before_level = ob_get_level();
$plugin->start_html_buffer();
ccf_test_assert(
    $before_level === ob_get_level(),
    'HTML buffering should not start when no active widget has configured classes'
);

ccf_test_configure_widget([
    '_ccf_css_classes' => 'alpha beta',
]);
$GLOBALS['ccf_test_fallback_enabled'] = false;
$before_level = ob_get_level();
$plugin->start_html_buffer();
ccf_test_assert(
    $before_level === ob_get_level(),
    'HTML fallback filter should be able to disable buffering'
);

$GLOBALS['ccf_test_fallback_enabled'] = true;
ob_start();
$outer_level = ob_get_level();
$plugin->start_html_buffer();
ccf_test_assert(
    $outer_level + 1 === ob_get_level(),
    'HTML buffering should start when active widgets need fallback classes'
);
echo '<html><body><div id="text-2" class="widget">Example</div></body></html>';
ob_end_flush();
$html = ob_get_clean();

ccf_test_assert(
    false !== strpos($html, 'class="widget alpha beta"'),
    'final HTML fallback should add classes to the matching widget id'
);

$release = CCF_Widget_CSS_Classes_Updater::parse_release([
    'draft' => false,
    'prerelease' => false,
    'tag_name' => 'v0.0.2',
    'body' => "Requires WordPress: 6.5\nRequires PHP: 8.0\n\nTest release.",
    'assets' => [[
        'name' => 'wordpress-widget-custom-css-classes.zip',
        'browser_download_url' => 'https://github.com/cemfirat/wordpress-widget-custom-css-classes/releases/download/v0.0.2/wordpress-widget-custom-css-classes.zip',
        'state' => 'uploaded',
        'size' => 1234,
    ]],
]);

ccf_test_assert(
    is_array($release) && '0.0.2' === $release['version'],
    'updater should accept only a complete stable release with the exact package asset'
);

ccf_test_assert(
    false === CCF_Widget_CSS_Classes_Updater::parse_release([
        'draft' => true,
        'prerelease' => false,
        'tag_name' => 'v0.0.2',
        'body' => "Requires WordPress: 6.5\nRequires PHP: 8.0",
        'assets' => [],
    ]),
    'updater must reject draft releases'
);

echo "OK\n";
