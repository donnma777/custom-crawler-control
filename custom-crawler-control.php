<?php
/**
 * Plugin Name: クローラー個別制御
 * Description: 投稿・固定ページの編集画面で「ALL拒否モード」を有効にすると、チェックしたクローラーのみアクセスを許可します。デフォルトはALL許可です。
 * Version: 1.2
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
            'group_label' => '1. Google (検索エンジン & AI)',
            'description' => 'コア検索、広告、画像、動画、そしてGeminiなどのGoogle AIクローラーを含みます。',
        ],
        
        // ★★★ 2. Microsoft / Bing (検索エンジン & 広告) ★★★
        'Bing_Core' => [
            'uas' => ['bingbot', 'adidxbot', 'BingPreview'], 
            'label' => 'Bing (Microsoft 検索 & 広告)',
            'group_label' => '2. Microsoft / Bing',
            'description' => 'Bingのコア検索クローラー、広告ボットを含みます。',
        ],
        
        // ★★★ 3. 主要AI / LLM クローラー ★★★
        'OpenAI' => [
            'uas' => ['GPTBot', 'ChatGPT-User', 'OAI-SearchBot'], 
            'label' => 'GPTBot (OpenAI / ChatGPT)',
            'group_label' => '3. 主要AI / LLM クローラー',
            'description' => 'GPT-4やChatGPTなどOpenAIのデータ収集ボットです。',
        ],
        'Anthropic' => [
            'uas' => ['ClaudeBot', 'Claude-Web', 'anthropic-ai'], 
            'label' => 'ClaudeBot (Anthropic)',
            'group_label' => '3. 主要AI / LLM クローラー',
            'description' => 'AnthropicのClaude向けデータ収集ボットです。',
        ],
        'Perplexity' => [
            'uas' => ['PerplexityBot', 'PPLXBot', 'PPLX'], 
            'label' => 'Perplexity AI (PPLX)',
            'group_label' => '3. 主要AI / LLM クローラー',
            'description' => 'Perplexity AIのデータ収集ボットです。',
        ],
        'CoHere' => [
            'uas' => ['CoHereBot', 'cohere-ai'],
            'label' => 'CoHere (AIサービス)',
            'group_label' => '3. 主要AI / LLM クローラー',
            'description' => 'CoHereのAIサービス向けデータ収集ボットです。',
        ],
        'AmazonAI' => [
            'uas' => ['Amazonbot'], 
            'label' => 'Amazonbot (Amazon AI / 検索)',
            'group_label' => '3. 主要AI / LLM クローラー',
            'description' => 'AmazonのAI/検索サービス向けデータ収集ボットです。',
        ],
        'Apple_Bot' => [
            'uas' => ['Applebot'], 
            'label' => 'Applebot (Apple)',
            'group_label' => '3. 主要AI / LLM クローラー',
            'description' => 'AppleのAIや検索サービス向けボットです。',
        ],

        // ★★★ 4. SNS / プレビュー系（超重要） ★★★
        'Meta_SNS' => [
            'uas' => ['facebookexternalhit', 'Facebot'], 
            'label' => 'Meta / Facebook (OGP/プレビュー)',
            'group_label' => '4. SNS / プレビュー系',
            'description' => 'Facebook, InstagramなどMetaサービスでのリンクプレビューに必須です。',
        ],
        'X_SNS' => [
            'uas' => ['Twitterbot', 'Xbot'], 
            'label' => 'X / Twitter (プレビュー)',
            'group_label' => '4. SNS / プレビュー系',
            'description' => 'X (旧Twitter) でのリンクプレビューに必須です。Grokなども含まれます。',
        ],
        'LINE_SNS' => [
            'uas' => ['LineBot', 'LinePreview', 'LineExtractor'], 
            'label' => 'LINE (プレビュー)',
            'group_label' => '4. SNS / プレビュー系',
            'description' => 'LINEアプリ内でのリンクプレビューに必須です。',
        ],
        'LinkedIn_SNS' => [
            'uas' => ['LinkedInBot', 'linkedinbot'],
            'label' => 'LinkedIn (プレビュー)',
            'group_label' => '4. SNS / プレビュー系',
            'description' => 'LinkedInでのリンクプレビューに必須です。',
        ],
        'Pinterest_SNS' => [
            'uas' => ['Pinterestbot'],
            'label' => 'Pinterest (プレビュー)',
            'group_label' => '4. SNS / プレビュー系',
            'description' => 'Pinterestでの画像・リンクプレビューに必須です。',
        ],
        
        // ★★★ 5. SEO / 分析 / 監視ツール ★★★
        'Ahrefs' => [
            'uas' => ['AhrefsBot', 'AhrefsSiteAudit'], 
            'label' => 'AhrefsBot (SEO分析)',
            'group_label' => '5. SEO / 分析 / 監視ツール',
            'description' => '被リンクや競合分析で使われるAhrefsのボットです。',
        ],
        'Semrush' => [
            'uas' => ['SemrushBot'], 
            'label' => 'SemrushBot (SEO分析)',
            'group_label' => '5. SEO / 分析 / 監視ツール',
            'description' => 'SEO分析ツールSemrushのボットです。',
        ],
        'Majestic' => [
            'uas' => ['MJ12bot'], 
            'label' => 'MJ12bot (Majestic SEO)',
            'group_label' => '5. SEO / 分析 / 監視ツール',
            'description' => 'Majestic SEOのボットです。',
        ],
        'DotBot' => [
            'uas' => ['DotBot'], 
            'label' => 'DotBot (Moz)',
            'group_label' => '5. SEO / 分析 / 監視ツール',
            'description' => 'Mozのボットです。',
        ],
        'OtherSEO' => [
            'uas' => ['SEOkicks', 'BLEXBot', 'DataForSeoBot'],
            'label' => 'その他のSEOツール',
            'group_label' => '5. SEO / 分析 / 監視ツール',
            'description' => 'その他の主要なSEO・分析ツールボットです。',
        ],

        // ★★★ 6. その他検索エンジン & アーカイブ ★★★
        'Yandex_Core' => [
            'uas' => ['YandexBot', 'YandexImages', 'YandexVideo'], 
            'label' => 'Yandex (ロシア検索)',
            'group_label' => '6. その他検索エンジン & アーカイブ',
            'description' => 'ロシアの主要検索エンジンYandexのボットです。',
        ],
        'Chinese_Search' => [
            'uas' => ['Baiduspider', 'Sogou web spider', 'Sogou News Spider', '360Spider', 'YisouSpider', 'PetalBot'],
            'label' => '中国圏主要検索',
            'group_label' => '6. その他検索エンジン & アーカイブ',
            'description' => 'Baiduなど、中国圏の主要な検索エンジンボットです。',
        ],
        'ByteDance' => [
            'uas' => ['Bytespider'],
            'label' => 'ByteDance/TikTok',
            'group_label' => '6. その他検索エンジン & アーカイブ',
            'description' => 'TikTokを運営するByteDance社のボットです。',
        ],
        'OtherSearch' => [
            'uas' => ['DuckDuckBot', 'Slurp', 'Qwantify'],
            'label' => 'その他検索エンジン',
            'group_label' => '6. その他検索エンジン & アーカイブ',
            'description' => 'DuckDuckGoなど、その他の検索エンジンボットです。',
        ],
        'Archive' => [
            'uas' => ['archive.org_bot', 'CCBot', 'ia_archiver'],
            'label' => 'ウェブアーカイブ / データ収集',
            'group_label' => '6. その他検索エンジン & アーカイブ',
            'description' => 'Internet Archiveなど、ウェブデータを収集・保存するボットです。',
        ],

        // ★★★ 7. WordPress 内部処理 ★★★
        'WordPress_Internal' => [
            'uas' => ['WordPress'],
            'label' => 'WordPress (内部/pingback)',
            'group_label' => '7. WordPress 内部処理',
            'description' => 'WordPressの内部処理やPingbackなどで使用されるUser-Agentです。',
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

    // ALL拒否ON → 個別有効
    $controls_enabled = ($deny_mode_active === 'yes');

    // disabled属性を作る
    $disabled_attr = $controls_enabled ? '' : 'disabled="disabled"';

    // グレーアウト時の視覚効果
    $panel_style = $controls_enabled 
        ? 'opacity: 1;' 
        : 'opacity: 0.45; pointer-events: none;';  
    ?>

    <label for="ggc_deny_mode_active_field" style="display:block; margin-bottom:10px; padding:5px; background:#f9f9f9; border:1px solid #ccc;">
        <input 
            type="checkbox" 
            name="ggc_deny_mode_active_field" 
            id="ggc_deny_mode_active_field" 
            value="yes" 
            <?php checked($deny_mode_active, 'yes'); ?> 
        />
        <strong style="color: red;">【ALL拒否モードを有効にする】</strong>
    </label>

    <p>ALL拒否ONのときのみ、以下のチェックが有効になります。</p>

    <div id="ggc_crawlers_panel"
         style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding:10px; <?php echo esc_attr($panel_style); ?>"
         title="<?php echo $controls_enabled ? '' : 'ALL拒否モードがOFFのため編集できません'; ?>">

        <?php 
        $grouped_bots = [];
        // カテゴリごとにボットをグループ化
        foreach ($allowable_bots as $key => $data) {
            $group_key = $data['group_label'] ?? 'その他';
            $grouped_bots[$group_key][$key] = $data;
        }

        $group_index = 0; // NEW: インデックス初期化

        foreach ($grouped_bots as $group_label => $bots): 
            $group_index++; // NEW: インデックスをインクリメント
            
            // 予測可能な固定IDを生成: ggc-group-N (N=1, 2, 3...)
            $group_id = 'ggc-group-' . $group_index; 
            
            // 複数の項目が同じ group_label を持つ場合、descriptionは最初の項目から取得
            $group_desc = $bots[array_key_first($bots)]['description'] ?? '';
        ?>

        <h4 class="ggc-group-header" data-target="#<?php echo esc_attr($group_id); ?>" 
            style="cursor: pointer; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-top: 15px; margin-bottom: 10px;">
            <span class="dashicons dashicons-arrow-right-alt2 ggc-arrow" style="font-size: 16px; margin-right: 5px; transform: rotate(0deg); transition: transform 0.2s;"></span>
            <?php echo esc_html($group_label); ?>
            <small style="float: right; cursor: pointer; color: #0073aa;" class="ggc-toggle-all" data-group="<?php echo esc_attr($group_id); ?>">
                [全選択/解除]
            </small>
        </h4>

        <div id="<?php echo esc_attr($group_id); ?>" class="ggc-group-content" style="display: none; padding-bottom: 10px;">
            <?php if ($group_desc): ?>
                <p style="font-size: 11px; margin-top: 0; color: #666; font-style: italic;"><?php echo esc_html($group_desc); ?></p>
            <?php endif; ?>
            
            <?php foreach ($bots as $key => $data): ?>
                <label style="display: block; margin-bottom:5px;">
                    <input 
                        type="checkbox" 
                        name="ggc_allowed_crawlers[]"
                        value="<?php echo esc_attr($key); ?>"
                        <?php checked(in_array($key, $allowed_bots)); ?>
                        <?php echo $disabled_attr; ?>
                        class="ggc-allowed-crawler-checkbox <?php echo esc_attr($group_id); ?>" />
                    <?php echo esc_html($data['label']); ?>
                </label>
            <?php endforeach; ?>
        </div>

        <?php endforeach; ?>
        
    </div>

    <p style="font-size:11px; margin-top:5px; <?php echo $controls_enabled ? 'color:red;' : 'color:green;'; ?>">
        <?php echo $controls_enabled 
            ? 'ALL拒否モードが有効。未チェックのクローラーはブロックされます。' 
            : '現在はALL許可モードです。個別設定は無効化されています。'; ?>
    </p>

    <?php
}


// --------------------------------------------------------
// STEP 3: チェックボックスの値を保存
// --------------------------------------------------------
function ggc_save_crawler_meta_box($post_id) {

    if (!isset($_POST['ggc_crawler_control_nonce']) ||
        !wp_verify_nonce($_POST['ggc_crawler_control_nonce'], 'ggc_crawler_control_save')) {
        return $post_id;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
    if (!current_user_can('edit_post', $post_id)) return $post_id;

    // ALL拒否モードの保存
    $active_value = isset($_POST['ggc_deny_mode_active_field']) ? 'yes' : 'no';
    update_post_meta($post_id, '_ggc_deny_mode_active', $active_value);

    // ALL拒否 OFF の時は個別設定を保存しない（クリア）
    if ($active_value !== 'yes') {
        delete_post_meta($post_id, '_ggc_allowed_crawlers');
        return;
    }

    // ALL拒否 ON の場合だけ、個別選択を保存
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

/**
 * 管理画面でのみメタボックス制御用JSを読み込む
 */
function ggc_enqueue_admin_scripts($hook) {
    global $typenow;

    // 投稿と固定ページの編集画面でのみJSを読み込む
    if (in_array($hook, ['post.php', 'post-new.php']) && in_array($typenow, ['post', 'page'])) {
        
        // jQueryを依存関係として登録 (WordPressの標準機能を利用)
        wp_enqueue_script(
            'ggc-admin-meta-js', 
            plugin_dir_url(__FILE__) . 'js/admin-meta.js', 
            ['jquery'], // jQueryに依存
            '1.2', // バージョンを更新
            true // footerで読み込む
        );
    }
}
add_action('admin_enqueue_scripts', 'ggc_enqueue_admin_scripts');