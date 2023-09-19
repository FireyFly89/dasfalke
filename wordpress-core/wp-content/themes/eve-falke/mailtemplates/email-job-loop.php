
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
    <title><?php echo get_bloginfo( 'name', 'display' ); ?></title>
</head>
<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
<div id="wrapper" dir="ltr" style="background-color:#fafafa;margin:0;padding:0;-webkit-text-size-adjust:none!important;width:100%;">
    <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
        <tbody>
        <tr>
            <td align="center" valign="top">
                <div id="template_header_image" style="height:60px;background:#ffffff url('https://dasfalkepersonal.at/wp-content/themes/eve-falke/img/email-logo-left.png') no-repeat center left">
                </div>
                <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container" style="background-color:#ffffff;border-radius:0px">
                    <tbody>
                    <tr>
                        <td align="center" valign="top">
                            <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
                                <tbody>
                                <tr>
                                    <td valign="top" id="body_content" style="background-color:#fafafa">
                                        <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                            <tbody>
                                            <tr>
                                                <td valign="top" style="padding:40px 20px 40px">
                                                    <div id="body_content_inner" style="color:#333333;font-family:'Montserrat',Arial,Helvetica,sans-serif;font-size:14px;line-height:150%;text-align:left">
														<p style="display:block;margin:0 0 16px"><?php _e('Dear customer,', 'dasfalke-email'); ?></p>
														<p style="display:block;margin:0 0 30px"><?php _e('Here are the latest jobs for your Job Alert via e-mail:', 'dasfalke-email'); ?></p>
                                                        <?php while ($jobs_query->have_posts()) : ?>
                                                        <?php
                                                            $jobs_query->the_post();
                                                            $job_id = get_the_ID();
                                                            $payment_extent = get_post_meta($job_id, 'employment_payment_extent', true);
                                                            $bundleManager = new DFPBManager();
                                                        ?>

                                                            <article style="border-radius: 6px; padding: 15px; background: #fff; margin-top: 10px;">
                                                                <table>
                                                                    <tr>
                                                                        <td style="vertical-align: top; width: 100%;">
                                                                            <h3 style="font-size: 20px; color: #3E76EB; margin: 0;">
                                                                                <a href="<?php echo get_permalink(); ?>" style="color: #3E76EB; font-weight: 700; text-decoration: none;"><?php echo get_post_meta($job_id, 'job_title', true) ?></a>
                                                                            </h3>
                                                                            <span style="font-size: 14px; color: #959FAA; margin: 10px 0 0 0;"><?php //_e(get_location_string($job_id), 'dasfalke-profile') ?></span>
                                                                            <span style="display: inline-block; height: 7px; width: 7px; background-color: #959FAA; border-radius: 50%; margin: 0 5px;"></span>
                                                                            <span style="font-size: 14px; color: #959FAA; margin: 10px 0 0 0;"><?php echo (!empty($payment_extent) ? eve_get_formatted_price($payment_extent) . " " . get_salary_text_by_numtype(get_post_meta($job_id, 'employment_payment', true)) : "Not available"); ?></span>
                                                                            <div style="padding: 0 75px 0 0;">
                                                                                <p><?php
                                                                                    $job_content = strip_tags( get_post_meta($job_id, 'job_description', true) );
                                                                                    $job_content = substr($job_content, 0, 160) .'...';

                                                                                    echo $job_content; ?></p>
                                                                            </div>
                                                                        </td>
                                                                        <td style="vertical-align: top;">
                                                                            <div style="width: 60px; height: 60px; box-shadow: 0 2px 10px #ECF0F5; border-radius: 8px; font-size: 0;">
                                                                                <img style="width: 60px; height: 60px;" src="<?php echo get_user_avatar(get_the_author_meta('ID')) ?>" alt="">
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="font-size: 14px; color: #959FAA; margin: 10px 0 0 0;">

                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                                <div>
                                                                    <span style="color: #BDCEE0; font-size: 14px;"><?php echo $bundleManager->time_ago(get_the_time('U')); ?></span>
                                                                    <a style="float: right; text-transform: uppercase; font-size: 12px; font-weight: 700; color: #3E76EB; text-decoration: none;" href="<?php echo get_permalink(); ?>"><?php _e( 'View', 'dasfalke-profile'); ?></a>
                                                                </div>
                                                            </article>

                                                        <?php endwhile; ?>
														<p style="display:block;margin:0"><?php echo esc_html__( 'Best regards and good luck in search for a new job,', 'dasfalke-email' ); ?></p>
														<p style="display:block;margin:0 0 30px"><?php echo esc_html__( 'Das Falke Personal Team', 'dasfalke-email' ); ?></p>
                                                    </tr>
                                                    </tbody>
                                                </table>

                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>

                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div style="height:40px;background:#484F5A;"></div>
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background:#fff">
                            <tr>
                                <td valign="top" align="center">
                                    <table border="0" cellpadding="0" cellspacing="0" width="600">
                                        <tr>
                                            <td valign="middle" style="margin-top:20px;padding:0;border:0;color:#000;font-family:'Montserrat',Arial,Helvetica,sans-serif;font-size:14px;line-height:130%;text-align:center">
                                                <p style="padding:1em 0;"><a style="text-decoration:none" href="https://www.instagram.com/dasfalkepersonal.at/" target="_blank"><img src="https://dasfalkepersonal.at/wp-content/themes/eve-falke/img/email-insta-icon.png" nosend="1" border="0" width="22" height="22" alt="dasfalke@instagram" title="Instagram"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a style="text-decoration:none" href="https://www.facebook.com/dasfalkepersonal/" target="_blank"><img src="https://dasfalkepersonal.at/wp-content/themes/eve-falke/img/email-fb-icon.png" nosend="1" border="0" width="22" height="22" alt="dasfalke@facebook" title="Facebook"></a></p>
                                                <p style="padding:0;background-color:#fff;color:#dfeaed;font-weight:normal;"><a style="color:#000;font-weight:normal;text-decoration:none" href="https://dasfalkepersonal.at/datenschutzerklarung/" target="_blank">Datenschutzerklärung</a> | <a href="https://dasfalkepersonal.at/agb/" style="color:#000;font-weight:normal;text-decoration:none" target="_blank">AGB</a> | <a href="https://dasfalkepersonal.at/impressum/" style="color:#000;font-weight:normal;text-decoration:none" target="_blank">Impressum</a>
                                                </p>
                                                <p style="padding:1em 0;background-color:#fff"><a style="background: #3E76EB; border: 2px solid #3E76EB; -webkit-border-radius: 14px; border-radius: 14px; box-shadow: 0 3px 6px rgba(62,118,235,0.3); color: #ffffff; font-weight: bold; font-size: 12px; line-height: 1; outline: none; text-decoration: none; padding: 7px 15px; text-align: center;" href="https://dasfalkepersonal.at/kontakt/" target="_blank">KONTAKT &amp; HILFE</a>
                                                </p>
                                                <p style="padding:1em 0;background-color:#fff;color:#c0ccd9;font-weight:bold">© 2019 Das Falke Personal GmbH, alle Rechte vorbehalten.</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </body>
</html>