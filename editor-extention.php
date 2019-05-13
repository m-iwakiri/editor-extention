<?php
/*
Plugin Name: Editor Extention
Plugin URI: http://www.office-iwakiri.com/plugin
Description: 記事投稿画面を拡張するプラグイン
Author: m.iwakiri
Version: 0.2
Author URI: http://www.office-iwakiri.com
*/

class EditorExtention{
    /** 設定値 */
    private $options;

    function __construct(){
        // add_filter( 'the_editor', array( $this, 'add_the_content_editor_placeholder') );

        add_action( 'admin_footer-post.php', array($this, 'customize_editor') , 10 );
        add_action( 'admin_footer-post-new.php', array($this, 'customize_editor') , 10 );
        // 管理メニューに追加するフック
        add_action('admin_menu', array($this, 'add_pages'));
        // ページの初期化を行います。
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    //管理画面にメニューを追加
    function add_pages(){
        add_menu_page('投稿画面拡張', '投稿画面拡張', 'manage_options', 'ee_settings', array($this, 'extention_setting_create'), '',100);
    }

    function add_the_content_editor_placeholder( $the_content_editor_html ){
        if( 'post' === get_post_type() ){
            $placeholder = 'ここにはコラム記事の概要・導入文を記入します。';
            $the_content_editor_html = preg_replace( '/<textarea/', "<textarea placeholder=\"{$placeholder}\"", $the_content_editor_html );
        }
        return $the_content_editor_html;
    }

    /**
     * 設定ページの初期化を行います。
     */
    function page_init(){
        // 設定を登録します(入力値チェック用)。
        // register_setting( $option_group, $option_name, $sanitize_callback )
        //   $option_group      : 設定のグループ名
        //   $option_name       : 設定項目名(DBに保存する名前)
        //   $sanitize_callback : 入力値調整をする際に呼ばれる関数
        register_setting( 'ee_settings', 'ee_settings', array( $this, 'sanitize' ) );
 
        // 入力項目のセクションを追加します。
        // add_settings_section( $id, $title, $callback, $page )
        //   $id       : セクションのID
        //   $title    : セクション名
        //   $callback : セクションの説明などを出力するための関数
        //   $page     : 設定ページのslug (add_menu_page()の$menu_slugと同じものにする)
        add_settings_section( 'ee_settings_section_id', 'コラムコンテンツ設定', '', 'ee_settings' );
 
        // 入力項目のセクションに項目を1つ追加します(今回は「メッセージ」というテキスト項目)。
        // add_settings_field( $id, $title, $callback, $page, $section, $args )
        //   $id       : 入力項目のID
        //   $title    : 入力項目名
        //   $callback : 入力項目のHTMLを出力する関数
        //   $page     : 設定ページのslug (add_menu_page()の$menu_slugと同じものにする)
        //   $section  : セクションのID (add_settings_section()の$idと同じものにする)
        //   $args     : $callbackの追加引数 (必要な場合のみ指定)
        add_settings_field( 'title_length', 'タイトルの目標文字数', array( $this, 'title_length_callback' ), 'ee_settings', 'ee_settings_section_id' );
        // add_settings_field( 'main_length', '記事本文の目標文字数', array( $this, 'main_length_callback' ), 'ee_settings', 'ee_settings_section_id' );
        // add_settings_field( 'contents_length', 'コラムコンテンツの目標文字数', array( $this, 'contents_length_callback' ), 'ee_settings', 'ee_settings_section_id' );
        // add_settings_field( 'matome_length', 'まとめの目標文字数', array( $this, 'matome_length_callback' ), 'ee_settings', 'ee_settings_section_id' );
    }

    /**
     * 設定ページのHTMLを出力します。
     */
    function extention_setting_create(){
        // 設定値を取得します。
        $this->options = get_option( 'ee_settings' );
        ?>
<div class="wrap">
    <h2>投稿画面拡張</h2>
<?php
        global $parent_file;
        if ( $parent_file != 'options-general.php' ) {
            require(ABSPATH . 'wp-admin/options-head.php');
        }
?>
    <form method="post" action="options.php">
<?php
    // 隠しフィールドなどを出力します(register_setting()の$option_groupと同じものを指定)。
    settings_fields( 'ee_settings' );
    // 入力項目を出力します(設定ページのslugを指定)。
    do_settings_sections( 'ee_settings' );
    // 送信ボタンを出力します。
    submit_button();
?>
    </form>
</div><!-- end .wrap -->
<?php
    }
    /**
     * 入力項目(「タイトルの目標文字数」)のHTMLを出力します。
     */
    public function title_length_callback()
    {
        // 値を取得
        $value = isset( $this->options['title_length'] ) ? $this->options['title_length'] : '';
        // nameの[]より前の部分はregister_setting()の$option_nameと同じ名前にします。
        ?><input type="text" id="title_length" name="ee_settings[title_length]" value="<?php esc_attr_e( $value ) ?>" /><?php
    }
    /**
     * 入力項目(「記事本文の目標文字数」)のHTMLを出力します。
     */
    public function main_length_callback()
    {
        // 値を取得
        $value = isset( $this->options['main_length'] ) ? $this->options['main_length'] : '';
        // nameの[]より前の部分はregister_setting()の$option_nameと同じ名前にします。
        ?><input type="text" id="main_length" name="ee_settings[main_length]" value="<?php esc_attr_e( $value ) ?>" /><?php
    }
    /**
     * 入力項目(「コラムコンテンツの目標文字数」)のHTMLを出力します。
     */
    public function contents_length_callback()
    {
        // 値を取得
        $value = isset( $this->options['contents_length'] ) ? $this->options['contents_length'] : '';
        // nameの[]より前の部分はregister_setting()の$option_nameと同じ名前にします。
        ?><input type="text" id="contents_length" name="ee_settings[contents_length]" value="<?php esc_attr_e( $value ) ?>" /><?php
    }
    /**
     * 入力項目(「まとめの目標文字数」)のHTMLを出力します。
     */
    public function matome_length_callback()
    {
        // 値を取得
        $value = isset( $this->options['matome_length'] ) ? $this->options['matome_length'] : '';
        // nameの[]より前の部分はregister_setting()の$option_nameと同じ名前にします。
        ?><input type="text" id="matome_length" name="ee_settings[matome_length]" value="<?php esc_attr_e( $value ) ?>" /><?php
    }
    /**
     * 送信された入力値の調整を行います。
     *
     * @param array $input 設定値
     */
    public function sanitize( $input )
    {
        // DBの設定値を取得します。
        $this->options = get_option( 'ee_settings' );
 
        $new_input = array();
 
        // 入力がある場合値を調整
        // if( isset( $input['main_length'] ) && trim( $input['main_length'] ) !== '' && intval(trim( $input['main_length'] ), 10) > 0 ) {
        //     $new_input['main_length'] = sanitize_text_field( $input['main_length'] );
        // } else {
        // 入力がない場合エラーを出力
            // add_settings_error( $setting, $code, $message, $type )
            //   $setting : 設定のslug
            //   $code    : エラーコードのslug (HTMLで'setting-error-{$code}'のような形でidが設定されます)
            //   $message : エラーメッセージの内容
            //   $type    : メッセージのタイプ。'updated' (成功) か 'error' (エラー) のどちらか
            // add_settings_error( 'ee_settings', 'main_length', '記事本文の目標文字数：0以上の整数を入力して下さい。' ,'error');
 
            // 値をDBの設定値に戻します。
        //     $new_input['main_length'] = isset( $this->options['main_length'] ) ? $this->options['main_length'] : '0';
        // }
        // if( isset( $input['contents_length'] ) && trim( $input['contents_length'] ) !== '' && intval(trim( $input['contents_length'] ), 10) > 0 ) {
        //     $new_input['contents_length'] = sanitize_text_field( $input['contents_length'] );
        // } else {
        //     add_settings_error( 'ee_settings', 'contents_length', 'コラムコンテンツの目標文字数：0以上の整数を入力して下さい。' ,'error');
        //     $new_input['contents_length'] = isset( $this->options['contents_length'] ) ? $this->options['contents_length'] : '0';
        // }
        // if( isset( $input['matome_length'] ) && trim( $input['matome_length'] ) !== '' && intval(trim( $input['matome_length']), 10) > 0 ) {
        //     $new_input['matome_length'] = sanitize_text_field( $input['matome_length'] );
        // } else {
        //     add_settings_error( 'ee_settings', 'matome_length', 'まとめの目標文字数：0以上の整数を入力して下さい。' ,'error');
        //     $new_input['matome_length'] = isset( $this->options['matome_length'] ) ? $this->options['matome_length'] : '0';
        // }
        if( isset( $input['title_length'] ) && trim( $input['title_length'] ) !== '' && intval(trim( $input['title_length']), 10) > 0 ) {
            $new_input['title_length'] = sanitize_text_field( $input['title_length'] );
        } else {
            add_settings_error( 'ee_settings', 'title_length', 'タイトルの目標文字数：0以上の整数を入力して下さい。' ,'error');
            $new_input['title_length'] = isset( $this->options['title_length'] ) ? $this->options['title_length'] : '0';
        }

        return $new_input;
    }

    function customize_editor() {
        // 設定値を取得します。
        $options = get_option( 'ee_settings' );
        // $main_length = $options['main_length'];
        // $contents_length = $options['contents_length'];
        // $matome_length = $options['matome_length'];
        $title_length = $options['title_length'];

        global $post_type;
        if ( 'post' === $post_type ) :
            mb_http_output('UTF-8');
            echo '<style type="text/css">';
            echo <<< EOF
.smart-cf-icon-handle{
    /* overflow: unset; */
    width: auto;
    text-align: left;
}
.smart-cf-icon-handle small,
.smart-cf-repeat-btn small{
    font-size: 12px;
    vertical-align: 5px;
    color: #999999;
}
            
.words-count{
    color:#666;
    background-color:#ffffe0;
    padding: 0.25em 1.0em;
    border-radius:5px;
    border:1px solid #ccc;
    display: inline-block;
}
.words-count span{
    margin-left:5px;
}
.words-count strong{
    font-weight: bold;
    color: red;
}
#wp-word-count strong{
    font-weight: bold;
    color: red;
}
.mce-statusbar strong{
    font-weight: bold;
    color: red;
    font-size: 11px;
}            
EOF;
            echo '</style>';
            echo '<script type="text/javascript">';
            echo <<< EOF
jQuery(window).on('load', function() {
    function count_characters(in_sel, out_sel) {
        out_sel.html( in_sel.val().length );
    }

    //ページ表示に表示エリアを出力
    jQuery('#titlewrap').after('<div class="words-count">文字数<span class="wp-title-count">0</span>&emsp;<strong>（目標：$title_length 文字）</strong></div>');

    // jQuery("textarea[name='smart-custom-fields[matome-contents][0]']").after('<div class="words-count">文字数<span class="wp-matome-contents-count">0</span>&emsp;<strong>（目標：$matome_length 文字）</strong></div>');
    // jQuery("textarea[name^='smart-custom-fields[param-contents]']").each(function(){
    //     jQuery(this).after('<div class="words-count">文字数<span class="wp-param-contents-count">0</span>&emsp;<strong>（目標：$contents_length 文字）</strong></div>');
    // });


    //WYSIWYGの本文とカスタムフィールドにスタイルとテキスト指定
    // jQuery('#wp-word-count').append('&emsp;<strong>（目標：$main_length 文字）</strong>');

    //ページ表示時に数える
    count_characters(jQuery('#title'), jQuery('.wp-title-count'));

    // count_characters(jQuery("textarea[name='smart-custom-fields[matome-contents][0]']"), jQuery('.wp-matome-contents-count'));
    // jQuery("textarea[name^='smart-custom-fields[param-contents]']").each(function(){
    //     count_characters(jQuery(this), jQuery(this).siblings('.words-count').children('.wp-param-contents-count'));
    //     jQuery(this).bind("keydown keyup keypress change",function(){
    //         count_characters(jQuery(this), jQuery(this).siblings('.words-count').children('.wp-param-contents-count'));
    //     });
    // });


    //入力フォーム変更時に数える
    // jQuery("textarea[name='smart-custom-fields[matome-contents][0]']").bind("keydown keyup keypress change",function(){
        // count_characters(jQuery("textarea[name='smart-custom-fields[matome-contents][0]']"), jQuery('.wp-matome-contents-count'));
    // });

    // jQuery('.smart-cf-icon-handle').append('<small>ドラッグで移動</small>');
    // jQuery('.btn-add-repeat-group.smart-cf-repeat-btn').append('<small>追加</small>');
    // jQuery('.btn-remove-repeat-group.smart-cf-repeat-btn').append('<small>削除</small>');
    jQuery('#title').bind("keydown keyup keypress change",function(){
        count_characters(jQuery('#title'), jQuery('.wp-title-count'));
    });

});
EOF;
        echo '</script>';
        endif;
    }

}
if( is_admin() ) :
    $editor_extention = new EditorExtention;
endif;
?>