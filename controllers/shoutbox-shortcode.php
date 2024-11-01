<?php

class shoutbox2024_shortcode
{
    private $user_id;

    public function __construct()
    {
        $this->user_id = get_current_user_id();
        add_shortcode('shoutbox', array($this, 'shoutbox2024_shortcode'));
    }

    public function shoutbox2024_shortcode($atts)
    {
        if (!is_user_logged_in()) {
            $shoutbox_template = "<p>You must be logged in to use the shoutbox. <a href='".wp_login_url()."'>Login</a></p>";
            return $shoutbox_template;
        }

        $shoutboxHeight = get_option('shoutbox2024_height');

        $shoutbox_template = "
            <table class='table shoutbox_table'>
            <tbody>
                <tr>
                    <td valign='top'>
                        <div id='shoutbox-shouts' style='height:".$shoutboxHeight."px;overflow:auto'>

                            <div id='shoutbox-shouts-loading' style='display:none;'>
                                <div class='lds-ripple'><div></div><div></div></div>
                            </div>

                            <table id='shoutbox-shouts-table'>

                                <!-- Messages go here -->

                            </table>


                        </div>
                    </td>
                </tr>

                <tr>
                    <td valign='top' colspan='2'>
                        <div id='shoutbox-smilies-container'></div>
                        <div id='shoutbox-preferences-container'></div>
                        <!-- Container for if message is PM -->
                        <div id='shoutbox-pm-container' style='display:none;'>
                            PM to: <span id='shoutbox-pm-to'></span>
                            <button id='shoutbox-pm-cancel-button' class='input_submit alt'>Cancel</button> 
                        </div>
                        <input type='text' id='shoutbox-global-shout' size='70' class='input_text' />
                        <input type='button' id='shoutbox-submit-button' value='Shout' class='input_submit' />
                        <input type='button' id='shoutbox-clear-button' value='Clear' class='input_submit alt' />
                        <input type='button' id='shoutbox-refresh-button' value='Refresh' class='input_submit alt' />
                        <input type='button' id='shoutbox-smilies-button' value='😀' class='input_submit alt' />
                        <input type='button' id='shoutbox-preferences-button' value='Preferences' class='input_submit alt' />
                    </td>
                </tr>


            </tbody>
        </table>";

        return $shoutbox_template;
    }

}