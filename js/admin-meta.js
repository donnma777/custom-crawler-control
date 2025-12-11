jQuery(document).ready(function ($) {
    // ----------------------------------------------------------------------
    // 1. カテゴリ折りたたみ (アコーディオン)
    // ----------------------------------------------------------------------
    $('.ggc-group-header').on('click', function () {
        const target = $(this).data('target');
        // 矢印アイコンの回転
        $(this).find('.ggc-arrow').toggleClass('rotated');
        // コンテンツの表示/非表示を切り替え
        $(target).slideToggle(200);
    });

    // 初期状態で矢印のスタイルを追加
    $('<style>.ggc-arrow.rotated { transform: rotate(90deg) !important; }</style>').appendTo('head');

    // ----------------------------------------------------------------------
    // 2. カテゴリごとの全選択/全解除 
    // ----------------------------------------------------------------------
    $('.ggc-toggle-all').on('click', function (e) {
        e.stopPropagation(); // ヘッダーの折りたたみイベントを抑制

        const groupId = $(this).data('group'); // 例: 'ggc-group-2'

        // 対象となるチェックボックスを取得
        // .ggc-allowed-crawler-checkbox.ggc-group-1, .ggc-allowed-crawler-checkbox.ggc-group-2 の形式で取得
        const checkboxes = $(`.ggc-allowed-crawler-checkbox.${groupId}`);

        // 現在、全てチェックされているか確認 (非活性のものは除く)
        const enabledCheckboxes = checkboxes.filter(':enabled');

        // enabledかつcheckedの数が、enabledの総数と等しいか
        const allChecked = enabledCheckboxes.length > 0 &&
            enabledCheckboxes.length === enabledCheckboxes.filter(':checked').length;

        // 全てチェックされている場合は全解除、それ以外は全選択
        enabledCheckboxes.prop('checked', !allChecked);
    });

    // ----------------------------------------------------------------------
    // 3. ALL拒否モード と 個別クローラー制御 の連動ロジック 
    // ----------------------------------------------------------------------

    const mainCheckbox = $('#ggc_deny_mode_active_field');
    const panel = $('#ggc_crawlers_panel');
    const childCheckboxes = panel.find('.ggc-allowed-crawler-checkbox');

    if (mainCheckbox.length === 0 || panel.length === 0) {
        return;
    }

    // 制御の状態を切り替える関数
    function setControls(enabled) {
        childCheckboxes.each(function () {
            if (enabled) {
                $(this).prop('disabled', false).removeAttr('disabled');
            } else {
                $(this).prop('disabled', true).attr('disabled', 'disabled');
            }
        });

        if (enabled) {
            panel.css({
                'opacity': '1',
                'pointer-events': 'auto'
            }).removeAttr('title');
        } else {
            panel.css({
                'opacity': '0.45',
                'pointer-events': 'none'
            }).attr('title', 'ALL拒否モードがOFFのため編集できません');
        }

        // ALL拒否モードOFFで非活性化されたら、全てのカテゴリを折りたたむ
        if (!enabled) {
            $('.ggc-group-content').slideUp(200);
            $('.ggc-arrow').removeClass('rotated');
        }
    }

    // 1. 変更イベントを設定
    mainCheckbox.on('change', function () {
        setControls(this.checked);
    });

    // 2. ページロード時の初期化
    setControls(mainCheckbox.prop('checked'));

});