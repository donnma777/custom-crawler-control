<?php
/**
 * Plugin Name: クローラー個別制御
 * Description: 投稿・固定ページの編集画面で「ALL拒否モード」を有効にすると、チェックしたクローラーのみアクセスを許可します。デフォルトはALL許可です。
 * Version: 1.0
 * Author: donnma
 * Author URI: https://donnma.com/
 * Plugin URI: https://github.com/donnma777/custom-crawler-control
 * GitHub: https://github.com/donnma777
 * X: https://x.com/donnma777
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// --------------------------------------------------------
// グローバル設定：許可可能なクローラーの定義
// --------------------------------------------------------

function ggc_get_allowable_bots() {
    return [
        // ★★★ 1. Google (検索エンジン & AI) ★★★
        'Google_Core' => [
            'uas' => [
                'Googlebot', 'AdsBot-Google', 'Mediapartners-Google',
                'Google-Extended', 'GoogleOther', 'GoogleOther-Image', 
                'GoogleOther-Video', 'GoogleOther-Mobile', 'Gemini',
            ], 
            'label' => 'Google (検索エンジン & AI全般)',
        ],
        
        // ★★★ 2. Microsoft / Bing (検索エンジン & 広告) ★★★
        'Bing_Core' => [
            'uas' => ['bingbot', 'adidxbot', 'BingPreview'], 
            'label' => 'Bing (Microsoft 検索 & 広告)',
        ],
        
        // ★★★ 3. 主要AI / LLM クローラー ★★★
        'OpenAI' => [
            'uas' => ['GPTBot', 'ChatGPT-User', 'OAI-SearchBot'], 
            'label' => 'GPTBot (OpenAI / ChatGPT)',
        ],
        'Anthropic' => [
            'uas' => ['ClaudeBot', 'Claude-Web', 'anthropic-ai'], 
            'label' => 'ClaudeBot (Anthropic)',
        ],
        'Perplexity' => [
            'uas' => ['PerplexityBot', 'PPLXBot', 'PPLX'], 
            'label' => 'Perplexity AI (PPLX)',
        ],
        'CoHere' => [
            'uas' => ['CoHereBot', 'cohere-ai'],
            'label' => 'CoHere (AIサービス)',
        ],
        'AmazonAI' => [
            'uas' => ['Amazonbot'], 
            'label' => 'Amazonbot (Amazon AI / 検索)',
        ],
        'Apple_Bot' => [
            'uas' => ['Applebot'], 
            'label' => 'Applebot (Apple)',
        ],

        // ★★★ 4. SNS / プレビュー系（超重要） ★★★
        'Meta_SNS' => [
            'uas' => ['facebookexternalhit', 'Facebot'], 
            'label' => 'Meta / Facebook (OGP/プレビュー)',
        ],
        'X_SNS' => [
            'uas' => ['Twitterbot', 'Xbot'], 
            'label' => 'X / Twitter (プレビュー)',
        ],
        'LINE_SNS' => [
            'uas' => ['LineBot', 'LinePreview', 'LineExtractor'], 
            'label' => 'LINE (プレビュー)',
        ],
        'LinkedIn_SNS' => [
            'uas' => ['LinkedInBot', 'linkedinbot'],
            'label' => 'LinkedIn (プレビュー)',
        ],
        'Pinterest_SNS' => [
            'uas' => ['Pinterestbot'],
            'label' => 'Pinterest (プレビュー)',
        ],
        
        // ★★★ 5. SEO / 分析 / 監視ツール ★★★
        'Ahrefs' => [
            'uas' => ['AhrefsBot', 'AhrefsSiteAudit'], 
            'label' => 'AhrefsBot (SEO分析)',
        ],
        'Semrush' => [
            'uas' => ['SemrushBot'], 
            'label' => 'SemrushBot (SEO分析)',
        ],
        'Majestic' => [
            'uas' => ['MJ12bot'], 
            'label' => 'MJ12bot (Majestic SEO)',
        ],
        'DotBot' => [
            'uas' => ['DotBot'], 
            'label' => 'DotBot (Moz)',
        ],
        'OtherSEO' => [
            'uas' => ['SEOkicks', 'BLEXBot', 'DataForSeoBot'],
            'label' => 'その他のSEOツール',
        ],

        // ★★★ 6. その他検索エンジン & アーカイブ ★★★
        'Yandex_Core' => [
            'uas' => ['YandexBot', 'YandexImages', 'YandexVideo'], 
            'label' => 'Yandex (ロシア検索)',
        ],
        'Chinese_Search' => [
            'uas' => ['Baiduspider', 'Sogou web spider', 'Sogou News Spider', '360Spider', 'YisouSpider', 'PetalBot'],
            'label' => '中国圏主要検索',
        ],
        'ByteDance' => [
            'uas' => ['Bytespider'],
            'label' => 'ByteDance/TikTok',
        ],
        'OtherSearch' => [
            'uas' => ['DuckDuckBot', 'Slurp', 'Qwantify'],
            'label' => 'その他検索エンジン',
        ],
        'Archive' => [
            'uas' => ['archive.org_bot', 'CCBot', 'ia_archiver'],
            'label' => 'ウェブアーカイブ / データ収集',
        ],

        // ★★★ 7. WordPress 内部処理 ★★★
        'WordPress_Internal' => [
            'uas' => ['WordPress'],
            'label' => 'WordPress (内部/pingback)',
        ],
    ];
}

// --------------------------------------------------------
// STEP 1 & 2: 記事編集画面にメタボックス（設定欄）
// --------------------------------------------------------
function ggc_add_crawler_meta_box() {
    $screens = ['post', 'page'];
    foreach ($screens as $screen) {
        add_meta_box(
            'ggc_crawler_control_box',
            '🔒 クローラーアクセス制御設定',
            'ggc_show_crawler_meta_box',
            $screen,
            'side',
            'high'
        );
    }
}
add_action('add_meta_boxes', 'ggc_add_crawler_meta_box');


function ggc_show_crawler_meta_box($post) {
    wp_nonce_field('ggc_crawler_control_save', 'ggc_crawler_control_nonce');

    $deny_mode_active = get_post_meta($post->ID, '_ggc_deny_mode_active', true);
    $allowed_bots = get_post_meta($post->ID, '_ggc_allowed_crawlers', true);
    if (!is_array($allowed_bots)) {
        $allowed_bots = [];
    }

    $allowable_bots = ggc_get_allowable_bots();
    
    ?>
    
    <label for="ggc_deny_mode_active_field" style="display: block; margin-bottom: 10px; padding: 5px; background: #f9f9f9; border: 1px solid #ccc;">
        <input 
            type="checkbox" 
            name="ggc_deny_mode_active_field" 
            id="ggc_deny_mode_active_field" 
            value="yes" 
            <?php checked($deny_mode_active, 'yes'); ?> 
        />
        <strong style="color: red;">【ALL拒否モードを有効にする】</strong>
    </label>
    
    <p>上記チェックを入れた場合のみ、下記リストが適用されます。</p>
    <p>アクセスを**許可したいクローラー**にチェックを入れてください（例外設定）。</p>
    
    <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
    <?php foreach ($allowable_bots as $key => $data): ?>
        <label style="display: block; margin-bottom: 5px;">
            <input 
                type="checkbox" 
                name="ggc_allowed_crawlers[]"
                value="<?php echo esc_attr($key); ?>"
                <?php checked(in_array($key, $allowed_bots)); ?> 
            />
            <?php echo esc_html($data['label']); ?>
        </label>
    <?php endforeach; ?>
    </div>
    
    <?php if ($deny_mode_active === 'yes'): ?>
    <p style="font-size: 11px; margin-top: 5px; color: red;">
        現在、このページはALL拒否モードが有効です。チェックのないクローラーはブロックされます。
    </p>
    <?php else: ?>
    <p style="font-size: 11px; margin-top: 5px; color: green;">
        現在、このページは制御無効（デフォルト: ALL許可）です。
    </p>
    <?php endif;
}


// --------------------------------------------------------
// STEP 3: チェックボックスの値を保存
// --------------------------------------------------------
function ggc_save_crawler_meta_box($post_id) {
    // セキュリティチェック
    if (!isset($_POST['ggc_crawler_control_nonce']) || !wp_verify_nonce($_POST['ggc_crawler_control_nonce'], 'ggc_crawler_control_save')) {
        return $post_id;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    $active_value = isset($_POST['ggc_deny_mode_active_field']) ? 'yes' : 'no';
    update_post_meta($post_id, '_ggc_deny_mode_active', $active_value);

    $new_allowed_bots = isset($_POST['ggc_allowed_crawlers']) ? (array) $_POST['ggc_allowed_crawlers'] : [];
    update_post_meta($post_id, '_ggc_allowed_crawlers', $new_allowed_bots);
}
add_action('save_post', 'ggc_save_crawler_meta_box');


// --------------------------------------------------------
// STEP 4: 実際のアクセス制御ロジック
// --------------------------------------------------------
/**
 * ブラウザ誤判定を避けるための厳格なチェック
 * @param string $user_agent
 * @return bool
 */
function ggc_is_likely_browser($user_agent) {
    if (stripos($user_agent, 'Mozilla') === false) {
        return false;
    }

    return (
        stripos($user_agent, 'Chrome') !== false ||
        stripos($user_agent, 'Safari') !== false ||
        stripos($user_agent, 'Firefox') !== false ||
        stripos($user_agent, 'Edge') !== false ||
        stripos($user_agent, 'OPR/') !== false
    );
}

/**
 * 実際のブロッキング処理と排他処理
 */
function ggc_perform_blocking() {
    if (!is_singular()) { 
        return;
    }
    
    global $post;
    $post_id = $post->ID;

    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    // UAが空の場合は即時ブロック
    if ($user_agent === '') {
        header('HTTP/1.0 403 Forbidden');
        wp_die('User-Agent が空のアクセスは許可されていません。', 'アクセス禁止', ['response' => 403]);
    }

    // 1. ALL拒否モードのメインスイッチがONか確認
    $deny_mode_active = get_post_meta($post_id, '_ggc_deny_mode_active', true);
    if ($deny_mode_active !== 'yes') { 
        return; // デフォルトのALL許可
    }

    // --- ALL拒否モードが有効な場合の処理 ---
    
    $allowed_keys = get_post_meta($post_id, '_ggc_allowed_crawlers', true);
    if (!is_array($allowed_keys)) {
        $allowed_keys = [];
    }

    $all_allowable_bots = ggc_get_allowable_bots();
    $is_allowed = false;
    
    // 3. 許可リストに含まれるUAパターン全てに対してチェックを実行
    foreach ($allowed_keys as $key) {
        if (isset($all_allowable_bots[$key]['uas'])) {
            foreach ($all_allowable_bots[$key]['uas'] as $ua_pattern) {
                if (stripos($user_agent, $ua_pattern) !== false) {
                    $is_allowed = true; // 許可リストに含まれていた
                    break 2; // 外側のループも終了
                }
            }
        }
    }

    // 4. 許可リストに含まれていない場合、ボットであるかを判定しブロック
    if (!$is_allowed) {
        
        // 厳格なブラウザ判定チェック
        if (!ggc_is_likely_browser($user_agent)) {
            
            $message = 'このコンテンツは、ALL拒否モードにより、許可されたクローラー以外のアクセスが禁止されています。';
            
            // APIやJSONリクエストの場合はテキスト/JSON応答に切り替え
            if (function_exists('wp_is_json_request') && wp_is_json_request()) {
                wp_send_json_error(['code' => 'crawler_forbidden', 'message' => $message], 403);
            } else if (stripos(isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '', 'application/json') !== false) {
                 header('Content-Type: application/json', true, 403);
                 echo json_encode(['code' => 'crawler_forbidden', 'message' => $message]);
                 exit; 
            } else {
                // 標準的なHTMLリクエストの場合
                wp_die($message, 'アクセス禁止', ['response' => 403]);
            }
        }
    }
}
add_action('wp', 'ggc_perform_blocking');