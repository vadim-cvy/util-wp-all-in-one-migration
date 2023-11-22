<?php
namespace Cvy\WP\AllInOneMigration;
use Exception;

if ( ! defined( 'ABSPATH' ) ) exit;

class Main extends \Cvy\DesignPatterns\Singleton
{
  private string $_app_root_dir;

  static public function get_instance() : Main
  {
    return parent::get_instance();
  }

  protected function __construct()
  {
    add_action( 'admin_print_footer_scripts', fn() => $this->print_admin_js() );
  }

  private function print_admin_js() : void
  {
    echo <<<EOF
      <script>
        jQuery( '#ai1wm-export-form .ai1wm-accordion' ).addClass( 'ai1wm-open' )

        jQuery(
          `#ai1wm-no-spam-comments,
          #ai1wm-no-post-revisions,
          #ai1wm-no-media,
          #ai1wm-no-inactive-themes,
          #ai1wm-no-inactive-plugins,
          #ai1wm-no-cache`
        )
        .prop( 'checked', true )
        .trigger( 'change' );
      </script>
      EOF;
  }

  public function set_app_root_dir( string $app_root_dir ) : Main
  {
    $this->_app_root_dir = $app_root_dir;

    return $this;
  }

  private function get_app_root_dir() : string
  {
    if ( ! $this->_app_root_dir )
    {
      throw new Exception( 'App root dir is not set! Use %s::set_app_root_dir() to set one.' );
    }

    return $this->_app_root_dir;
  }

  public function add_base_export_exclusions() : Main
  {
    $this->add_export_exclusions([
      '.vscode',

      '.git',
      '.gitignore',

      'composer.json',
      'composer.lock',

      'package.json',
      'package.lock',
      'node_modules',

      'gulpfile.js',

      'tsconfig.json',

      'README.md',
    ]);

    return $this;
  }

  public function add_export_exclusions( array $rel_pathes ) : Main
  {
    $pathes = array_map(
      fn( $rel_path ) => basename( $this->get_app_root_dir() ) . $rel_path,
      $rel_pathes
    );

    add_filter(
      $this->get_export_filters_hook_name(),
      fn( $excludes ) => array_merge( $excludes, $pathes )
    );

    return $this;
  }

  private function get_export_filters_hook_name() : string
  {
    // "plugins" or "themes"
    $app_type = basename( dirname( $this->get_app_root_dir() ) );

    return "ai1wm_exclude_{$app_type}_from_export";
  }
}