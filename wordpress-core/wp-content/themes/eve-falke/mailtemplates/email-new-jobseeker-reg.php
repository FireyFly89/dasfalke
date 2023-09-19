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
																		<td valign="top" style="padding:40px 20px 0">
																			<div id="body_content_inner" style="color:#333333;font-family:'Montserrat',Arial,Helvetica,sans-serif;font-size:14px;line-height:150%;text-align:left">
																				<h2 style="text-align:center;font-size:18px"><?php _e('Welcome to Das Falke Personal GmbH!', 'dasfalke-email'); ?></h2>
																				<p style="display:block;margin:0 0 16px"><?php _e('Dear customer,', 'dasfalke-email'); ?></p>
																				<p style="display:block;margin:0 0 16px"><?php _e('Thank you for registering on the Jobs & Career Platform of Das Falke Personal GmbH! From now on all functions are available for your job search.', 'dasfalke-email'); ?></p>
																				<p style="text-align:center;margin:30px 0;"><a style="background: #3E76EB; border: 2px solid #3E76EB; -webkit-border-radius: 14px; border-radius: 14px; box-shadow: 0 3px 6px rgba(62,118,235,0.3); color: #ffffff; font-weight: bold; font-size: 12px; line-height: 1; outline: none; text-decoration: none; padding: 7px 15px; text-align: center;" href="https://dasfalkepersonal.at/my-account/" target="_blank"><?php echo esc_html__( 'MY ACCOUNT', 'dasfalke-email' ); ?></a></p>
																				<p style="display:block;margin:0"><?php echo esc_html__( 'Best regards and good luck,', 'dasfalke-email' ); ?></p>
																				<p style="display:block;margin:0 0 16px"><?php echo esc_html__( 'Das Falke Personal Team', 'dasfalke-email' ); ?></p>
																			</div>
																		</td>
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