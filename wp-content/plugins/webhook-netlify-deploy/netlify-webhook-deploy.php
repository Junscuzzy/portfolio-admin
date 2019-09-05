<?php

/**
 *  @package Webhook Netlify Deploy
 */
/*
Plugin Name: Webhook Netlify Deploy
Plugin URI: http://github.com/lukethacoder/wp-webhook-netlify-deploy
Description: Adds a Build Website button that sends a webhook request to build a netlify hosted website when clicked
Version: 1.1.0
Author: Luke Secomb
Author URI: https://lukesecomb.digital
License: GPLv3 or later
Text Domain: webhook-netlify-deploy
*/

/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

defined( 'ABSPATH' ) or die('You do not have access to this file, sorry mate');

class deployWebhook {

    /**
    * Constructor
    *
    * @since 1.0.0
    **/
    public function __construct() {
    	// Hook into the admin menu
    	add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );

        // Add Settings and Fields
    	add_action( 'admin_init', array( $this, 'setup_sections' ) );
        add_action( 'admin_init', array( $this, 'setup_fields' ) );
        add_action( 'admin_footer', array( $this, 'run_the_mighty_javascript' ) );
        add_action( 'admin_bar_menu', array( $this, 'add_to_admin_bar' ), 90 );
    }

    /**
    * Main Plugin Page markup
    *
    * @since 1.0.0
    **/
    public function plugin_settings_page_content() {?>
    	<div class="wrap">
    		<h2>Webhook Netlify Deploy</h2>
            <hr>

            <h3>Build Website</h3>
            <button id="build_button" class="button button-primary" name="submit" type="submit">Build Site</button><br>
            <p id="build_status" style="font-size: 12px; margin: 0;"></p>
            <p style="font-size: 12px">*Do not abuse the Build Site button*</p><br>

            <hr>

            <h3>Deploy Status</h3>
            <button id="status_button" class="button button-primary" name="submit" type="submit" style="margin: 0 0 16px;">Get Deploys Status</button>

            <div style="margin: 0 0 16px;">
                <a id="build_img_link" href="">
                    <img id="build_img" src=""/>
                </a>
            </div>
            <div>
                <!-- <p id="deploy_status"></p> -->
                <p id="deploy_id"></p>
                <div style="display: flex;"><p id="deploy_finish_time"></p><p id="deploy_loading"></p></div>
                <p id="deploy_ssl_url"></p>
            </div>

            <div id="deploy_preview"></div>

            <hr>

            <h3>Previous Builds</h3>
            <button id="previous_deploys" class="button button-primary" name="submit" type="submit" style="margin: 0 0 16px;">Get All Previous Deploys</button>
            <ul id="previous_deploys_container" style="list-style: none;"></ul>
    	</div> <?php
    }

    /**
    * Developer Settings (subpage) markup
    *
    * @since 1.0.0
    **/
    public function plugin_settings_subpage_content() {?>
    	<div class="wrap">
    		<h1>Developer Settings</h1>
    		<p>Do not change this if you dont know what you are doing</p>
            <hr>

            <?php
            if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ){
                  $this->admin_notice();
            } ?>
    		<form method="POST" action="options.php">
                <?php
                    settings_fields( 'deploy_webhook_fields' );
                    do_settings_sections( 'deploy_webhook_fields' );
                    submit_button();
                ?>
    		</form>

            <footer>
                <h3>Extra Info</h3>
                <p><a href="https://github.com/lukethacoder/wp-webhook-netlify-deploy">Plugin Docs</a></p>
                <p><a href="https://github.com/lukethacoder/wp-webhook-netlify-deploy">Project Github</a></p>
            </footer>

    	</div> <?php
    }

    
    /**
    * The Mighty JavaScript
    *
    * @since 1.0.0
    **/
    public function run_the_mighty_javascript() {
        // TODO: split up javascript to allow to be dynamically imported as needed
        // $screen = get_current_screen();
        // if ( $screen && $screen->parent_base != 'deploy_webhook_fields' && $screen->parent_base != 'deploy_webhook_fields_sub' ) {
        //     return;
        // }
        ?>
        <script type="text/javascript" >
        console.log('run_the_mighty_javascript');
        jQuery(document).ready(function($) {
            var _this = this;
            $( ".webhook-deploy_page_deploy_webhook_fields_sub td > input" ).css( "width", "100%");

            var webhook_url = '<?php echo(get_option('webhook_address')) ?>';
            var netlify_user_agent = '<?php echo(get_option('netlify_user_agent')) ?>';
            var netlify_api_key = '<?php echo(get_option('netlify_api_key'))?>'
            var netlify_site_id = '<?php echo(get_option('netlify_site_id')) ?>';

            var netlifySites = "https://api.netlify.com/api/v1/sites/";
            var req_url = netlifySites + netlify_site_id + '/deploys?access_token=' + netlify_api_key;

            function getDeployData() {
                $.ajax({
                    type: "GET",
                    url: req_url
                }).done(function(data) {
                    appendStatusData(data[0]);
                })
                .fail(function() {
                    console.error("error res => ", this)
                })
            }

            function getAllPreviousBuilds() {
                $.ajax({
                    type: "GET",
                    url: req_url
                }).done(function(data) {
                    var buildNo = 1;
                    data.forEach(function(item) {
                        var deploy_preview_url = '';
                        if (data.deploy_ssl_url) {
                            deploy_preview_url = data.deploy_ssl_url
                        } else {
                            deploy_preview_url = data.deploy_url
                        }
                        $('#previous_deploys_container').append(
                            '<li style="margin: 0 auto 16px"><hr><h3>No: ' + buildNo + ' - ' + item.name + '</h3><h4>Created at: ' +  new Date(item.created_at.toString()).toLocaleString() + '</h4><h4>' + item.title + '</h4><p>Id: ' + item.id + '</p><p>Deploy Time: ' + item.deploy_time + '</p><p>Branch: ' + item.branch + '</p><a href="' + item.deploy_preview_url + '">Preview Build</a></li>'
                        );
                        buildNo++;
                    })
                })
                .fail(function() {
                    console.error("error res => ", this)
                })
            }

            function runSecondFunc() {
                $.ajax({
                    type: "GET",
                    url: req_url
                }).done(function(data) {
                    $( "#build_img_link" ).attr("href", `${data.admin_url}`);
                    // $( "#build_img" ).attr("src", `https://api.netlify.com/api/v1/badges/${ netlify_site_id }/deploy-status`);
                })
                .fail(function() {
                    console.error("error res => ", this)
                })

                // $( "#build_status" ).html('Deploy building');
            }

            function appendStatusData(data) {
                var d = new Date();
                var p = d.toLocaleString();
                var yo = new Date(data.created_at);
                var created = yo.toLocaleString();
                var current_state = data.state;

                if (data.state === 'ready') {
                    current_state = "Success"
                }

                if (data.state !== 'ready') {
                    $( "#deploy_finish_time" ).html( "Building Site" );
                    $( "#build_img" ).attr("src", `https://api.netlify.com/api/v1/badges/${ netlify_site_id }/deploy-status`);
                    var dots = window.setInterval( function() {
                        var wait = document.getElementById('deploy_loading');
                        if ( wait.innerHTML.length >= 3 ) {
                            wait.innerHTML = "";
                        }
                        else {
                            wait.innerHTML += ".";
                        }
                    },
                    500);
                } else {
                    var deploy_preview_url = '';

                    if (data.deploy_ssl_url) {
                        deploy_preview_url = data.deploy_ssl_url
                    } else {
                        deploy_preview_url = data.deploy_url
                    }

                    $( "#deploy_id" ).html( "ID: " + data.id + "" );
                    $( "#deploy_finish_time" ).html( "Build Completed: " + created );
                    $( "#build_img" ).attr("src", `https://api.netlify.com/api/v1/badges/${ netlify_site_id }/deploy-status`);
                    $( "#deploy_ssl_url" ).html( "Deploy URL: <a href='" + deploy_preview_url + "'>" + data.deploy_ssl_url + "</a>");
                    $( "#deploy_preview" ).html( `<iframe style="width: 100%; min-height: 540px" id="frameLeft" src="${deploy_preview_url}"/>`)
                }


            }

            function netlifyDeploy() {
                return $.ajax({
                    type: "POST",
                    url: webhook_url,
                    dataType: "json",
                    header: {
                        "User-Agent": netlify_user_agent
                    }
                });
            }

            $("#status_button").on("click", function(e) {
                e.preventDefault();
                getDeployData();
            });

            $("#previous_deploys").on("click", function(e) {
                e.preventDefault();
                getAllPreviousBuilds();
            });

            $("#build_button").on("click", function(e) {

                // hide deploy
                $('#build_img_link').attr('href', '');
                $('#build_img').attr('src', '');
                $('#deploy_id').html('');
                $('#deploy_finish_time').html('');
                $('#deploy_ssl_url').html('');
                $('#deploy_preview').html('');

                e.preventDefault();

                netlifyDeploy().done(function() {
                    console.log("success")
                    getDeployData();
                    $( "#build_status" ).html('Deploy building');
                })
                .fail(function() {
                    console.error("error res => ", this)
                    $( "#build_status" ).html('There seems to be an error with the build', this);
                })
            });

            $(document).on('click', '#wp-admin-bar-netlify-deploy-button', function(e) {
                e.preventDefault();

                var $button = $(this),
                    $buttonContent = $button.find('.ab-item:first');

                if ($button.hasClass('deploying') || $button.hasClass('running')) {
                    return false;
                }

                $button.addClass('running').css('opacity', '0.5');
                
                netlifyDeploy().done(function() {
                    var $badge = $('#admin-bar-netlify-deploy-status-badge');

                    $button.removeClass('running');
                    $button.addClass('deploying');

                    $buttonContent.find('.ab-label').text('Deploying…');

                    if ($badge.length) {
                        if (!$badge.data('original-src')) {
                            $badge.data('original-src', $badge.attr('src'));
                        }

                        $badge.attr('src', $badge.data('original-src') + '?updated=' + Date.now());
                    }
                })
                .fail(function() {
                    $button.removeClass('running').css('opacity', '1');
                    $buttonContent.find('.dashicons-hammer')
                        .removeClass('dashicons-hammer').addClass('dashicons-warning');

                    console.error("error res => ", this)
                })
            });
        });
        </script> <?php
    }

    /**
    * Plugin Menu Items Setup
    *
    * @since 1.0.0
    **/
    public function create_plugin_settings_page() {
        $run_deploys = apply_filters( 'netlify_deploy_capability', 'manage_options' );
        $adjust_settings = apply_filters( 'netlify_adjust_settings_capability', 'manage_options' );

        if ( current_user_can( $run_deploys ) ) {
            $page_title = 'Deploy to Netlify';
            $menu_title = 'Webhook Deploy';
            $capability = 'manage_options';
            $slug = 'deploy_webhook_fields';
            $callback = array( $this, 'plugin_settings_page_content' );
            $icon = 'dashicons-admin-plugins';
            $position = 100;

            add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
        }
        if ( current_user_can( $adjust_settings ) ) {
            $sub_page_title = 'Developer Settings';
            $sub_menu_title = 'Developer Settings';
            $sub_capability = 'manage_options';
            $sub_slug = 'deploy_webhook_fields_sub';
            $sub_callback = array( $this, 'plugin_settings_subpage_content' );

            add_submenu_page( $slug, $sub_page_title, $sub_menu_title, $sub_capability, $sub_slug, $sub_callback );

        }


    }

    /**
    * Notify Admin on Successful Update
    *
    * @since 1.0.0
    **/
    public function admin_notice() { ?>
        <div class="notice notice-success is-dismissible">
            <p>Your settings have been updated!</p>
        </div><?php
    }

    /**
    * Setup Sections
    *
    * @since 1.0.0
    **/
    public function setup_sections() {
        add_settings_section( 'main_section', 'Webhook Settings', array( $this, 'section_callback' ), 'deploy_webhook_fields' );
    }
    
    /**
    * Check it wont break on build and deploy 
    *
    * @since 1.0.0
    **/
    public function section_callback( $arguments ) {
    	switch( $arguments['id'] ){
    		case 'main_section':
    			echo 'The build and deploy status will not work without these fields entered corrently';
    			break;
    	}
    }
    
    /**
    * Fields used for developer input data
    *
    * @since 1.0.0
    **/
    public function setup_fields() {
        $fields = array(
        	array(
        		'uid' => 'webhook_address',
        		'label' => 'Webhook Build URL',
        		'section' => 'main_section',
        		'type' => 'text',
                'placeholder' => 'https://',
                'default' => '',
            ),
            array(
        		'uid' => 'netlify_site_id',
        		'label' => 'Netlify site_id',
        		'section' => 'main_section',
        		'type' => 'text',
                'placeholder' => 'e.g. 5b8e927e-82e1-4786-4770-a9a8321yes43',
                'default' => '',
            ),
            array(
        		'uid' => 'netlify_api_key',
        		'label' => 'Netlify API Key',
        		'section' => 'main_section',
        		'type' => 'text',
                'placeholder' => 'GET O-AUTH TOKEN',
                'default' => '',
        	),
            array(
        		'uid' => 'netlify_user_agent',
        		'label' => 'User-Agent Site Value',
        		'section' => 'main_section',
        		'type' => 'text',
                'placeholder' => 'Website Name (and-website-url.netlify.com)',
                'default' => '',
        	)
        );
    	foreach( $fields as $field ){
        	add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'deploy_webhook_fields', $field['section'], $field );
            register_setting( 'deploy_webhook_fields', $field['uid'] );
    	}
    }

    /**
    * Field callback for handling multiple field types
    *
    * @since 1.0.0
    * @param $arguments
    **/
    public function field_callback( $arguments ) {

        $value = get_option( $arguments['uid'] );

        if ( !$value ) {
            $value = $arguments['default'];
        }

        switch( $arguments['type'] ){
            case 'text':
            case 'password':
            case 'number':
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
                break;
            case 'textarea':
                printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value );
                break;
            case 'select':
            case 'multiselect':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $attributes = '';
                    $options_markup = '';
                    foreach( $arguments['options'] as $key => $label ){
                        $options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value[ array_search( $key, $value, true ) ], $key, false ), $label );
                    }
                    if( $arguments['type'] === 'multiselect' ){
                        $attributes = ' multiple="multiple" ';
                    }
                    printf( '<select name="%1$s[]" id="%1$s" %2$s>%3$s</select>', $arguments['uid'], $attributes, $options_markup );
                }
                break;
            case 'radio':
            case 'checkbox':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $options_markup = '';
                    $iterator = 0;
                    foreach( $arguments['options'] as $key => $label ){
                        $iterator++;
                        $options_markup .= sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], $arguments['type'], $key, checked( $value[ array_search( $key, $value, true ) ], $key, false ), $label, $iterator );
                    }
                    printf( '<fieldset>%s</fieldset>', $options_markup );
                }
                break;
        }
    }

    /**
    * Add Deploy Button and Deployment Status to admin bar
    *
    * @since 1.1.0
    **/
    public function add_to_admin_bar( $admin_bar ) {

        $see_deploy_status = apply_filters( 'netlify_status_capability', 'manage_options' );
        $run_deploys = apply_filters( 'netlify_deploy_capability', 'manage_options' );

        if ( current_user_can( $run_deploys ) ) {
            $webhook_address = get_option( 'webhook_address' );

            if ( $webhook_address ) {
                $button = array(
                    'id' => 'netlify-deploy-button',
                    'title' => '<div style="cursor: pointer;"><span class="ab-icon dashicons dashicons-hammer"></span> <span class="ab-label">Deploy site</span></div>'
                );

                $admin_bar->add_node( $button );
            }
        }

        if ( current_user_can( $see_deploy_status ) ) {
            $netlify_site_id = get_option( 'netlify_site_id' );

            if ( $netlify_site_id ) {
                $badge = array(
                    'id' => 'netlify-deploy-status-badge',
                    'title' => sprintf( '<div style="display: flex; height: 100%%; align-items: center;">
                            <img id="admin-bar-netlify-deploy-status-badge" src="https://api.netlify.com/api/v1/badges/%s/deploy-status" alt="Netlify deply status" style="width: auto; height: 16px;" />
                        </div>', $netlify_site_id )
                );

                $admin_bar->add_node( $badge );
            }
        }

    }

}

new deployWebhook;
?>
