<?php
  use prosys\model\UserEntity,
      prosys\core\common\Settings,
      prosys\core\common\Functions;

  /* @var $user UserEntity */
  /* @var $_LOGGED_USER UserEntity */
  echo Functions::handleMessagesAdmin();
?>
    <div class="container-fluid">
				<div class="row-fluid">
					<div class="span12">
						<h3 class="page-title">
              <?php echo $title; ?>
            </h3>
            <?php
              prosys\admin\view\View::addBreadcumb('Domů', 'home', './');
              prosys\admin\view\View::addBreadcumb($title, '', 'none');
              echo prosys\admin\view\View::getBreadcumbs();
            ?>
					</div>
				</div>
				<!-- BEGIN PAGE CONTENT-->
				<div class="row-fluid profile">
					<div class="span12">
						<!--BEGIN TABS-->
						<div class="tabbable tabbable-custom tabbable-full-width">
							<ul class="nav nav-tabs"> 
								<li data-tab="bm_tab_1_2"><a href="#tab_1_2" data-toggle="tab">Informace o profilu</a></li>
								<li data-tab="bm_tab_1_3"><a href="#tab_1_3" data-toggle="tab">Účet</a></li>
							</ul>
							<div class="tab-content">
								<div class="tab-pane profile-classic row-fluid" id="tab_1_2">
                  <div class="span2"><img src="<?php echo Settings::ADMIN_IMAGES . 'avatars/' . $user->avatar . '.jpg'; ?>" alt="obázek profilu uživatele <?php echo $user->login; ?>" /></div>
									<ul class="unstyled span10">
										<li><span><?php echo $this->_labels['login']; ?>:</span> <?php echo $user->login; ?></li>
										<li><span><?php echo $this->_labels['first_name']; ?>:</span> <?php echo $user->firstName; ?></li>
										<li><span><?php echo $this->_labels['last_name']; ?>:</span> <?php echo $user->lastName; ?></li>
										<li><span><?php echo $this->_labels['email']; ?>:</span> <a href="mailto:<?php echo $user->email; ?>"><?php echo $user->email; ?></a></li>
										<li><span><?php echo $this->_labels['phone']; ?>:</span> <?php echo $user->phone; ?></li>
                    <li><span><?php echo $this->_labels['userGroup']; ?>:</span> <?php echo (($userGroups) ? implode(', ', $userGroups) : ''); ?></li>
									</ul>
								</div>
								<!--tab_1_2-->
								<div class="tab-pane row-fluid profile-account" id="tab_1_3">
									<div class="row-fluid">
										<div class="span12">
											<div class="span3">
												<ul class="ver-inline-menu tabbable margin-bottom-10">
                          <?php
                            $manageUserInfo = $_LOGGED_USER->hasRight('partner', 'manage_user_info');
                            if ($manageUserInfo) {
                          ?>
													<li class="active"><a data-toggle="tab" href="#tab_1-1"><i class="icon-cog"></i>Uživatelské informace</a><span class="after"></span></li>
                          <?php
                            }
                          ?>
													<!-- li class=""><a data-toggle="tab" href="#tab_2-2"><i class="icon-picture"></i> Change Avatar</a></li -->
													<li<?php echo (($manageUserInfo) ? '': ' class="active"'); ?>><a data-toggle="tab" href="#tab_3-3"><i class="icon-lock"></i> Změna hesla</a></li>
													<li><a data-toggle="tab" href="#tab_4-4"><i class="icon-eye-open"></i> Přizpůsobení</a></li>
												</ul>
											</div>
											<div class="span9">
												<div class="tab-content">
													<div id="tab_1-1" class="tab-pane<?php echo (($manageUserInfo) ? ' active': ''); ?>">
														<div style="height: auto;" id="accordion1-1" class="accordion collapse">
                              <form action="index.php" method="post" class="user_information">
                                <input type="hidden" name="controller" value="user" />
                                <input type="hidden" name="action" value="saveProfile" />
                                <input type="hidden" name="back_to" value="" />
                                <input type="hidden" name="id" value="<?php echo $user->id; ?>" />
                                
																<label class="control-label" for="first_name"><?php echo $this->_labels['first_name']; ?></label>
																<input id="first_name" type="text" name="first_name" placeholder="<?php echo mb_strtolower($this->_labels['first_name']); ?>" class="m-wrap span8" value="<?php echo $user->firstName; ?>" />
                                
																<label class="control-label" for="last_name"><?php echo $this->_labels['last_name']; ?></label>
                                <input id="last_name" type="text" name="last_name" placeholder="<?php echo mb_strtolower($this->_labels['last_name']); ?>" class="m-wrap span8" value="<?php echo $user->lastName; ?>" />
                                
																<label class="control-label" for="phone"><?php echo $this->_labels['phone']; ?></label>
																<input id="phone" type="text" name="phone" placeholder="(+420)777 888 999" class="m-wrap span8" value="<?php echo $user->phone; ?>" />
                                
																<label class="control-label" for="email"><?php echo $this->_labels['email']; ?></label>
																<input id="email" type="text" name="email" placeholder="jan.novak@seznam.cz" class="m-wrap span8" value="<?php echo $user->email; ?>" />
                                
																<label class="control-label"><?php echo $this->_labels['userGroup']; ?></label>
                                <div class="text"><?php echo (($userGroups) ? implode(', ', $userGroups) : ''); ?></div>
                                
                                <p>&nbsp;</p>
																<div class="submit-btn">
																	<button type="submit" class="btn green" title="Aplikovat změny"><i class="icon-ok"></i> Použít</button>
																	<button type="reset" class="btn red" title="zrušit změny"><i class="icon-remove"></i> Zrušit</button>
																</div>
															</form>
														</div>
													</div>
                          
													<div id="tab_3-3" class="tab-pane<?php echo (($manageUserInfo) ? '': ' active'); ?>">
														<div style="height: auto;" id="accordion3-3" class="accordion collapse">
															<form action="index.php" method="post" class="user_information">
                                <input type="hidden" name="controller" value="user" />
                                <input type="hidden" name="action" value="saveProfile" />
                                <input type="hidden" name="back_to" value="" />
                                <input type="hidden" name="id" value="<?php echo $user->id; ?>" />
                                
																<label class="control-label" for="old_password"><?php echo $this->_labels['old_password']; ?></label>
																<input id="old_password" type="password" name="old_password" placeholder="<?php echo mb_strtolower($this->_labels['old_password']); ?>" class="m-wrap span4" />
                                
																<label class="control-label" for="password"><?php echo $this->_labels['new_password']; ?></label>
																<input id="password" type="password" name="password" placeholder="<?php echo mb_strtolower($this->_labels['new_password']); ?>" class="m-wrap span4" />
                                
                                <label class="control-label" for="repassword"><?php echo $this->_labels['repassword']; ?></label>
																<input id="repassword" type="password" name="repassword" placeholder="<?php echo mb_strtolower($this->_labels['repassword']); ?>" class="m-wrap span4" />
																<div class="submit-btn">
																	<button type="submit" class="btn green" title="Změnit heslo"><i class="icon-ok"></i> Změnit heslo</button>
																	<button type="reset" class="btn red" title="Zrušit změny"><i class="icon-remove"></i> Zrušit</button>
																</div>
															</form>
														</div>
													</div>
													<div id="tab_4-4" class="tab-pane">
														<div style="height: auto;" id="accordion4-4" class="accordion collapse">
															<form action="index.php" method="post" class="user_information">
                                <input type="hidden" name="controller" value="user" />
                                <input type="hidden" name="action" value="saveProfile" />
                                <input type="hidden" name="back_to" value="" />
                                <input type="hidden" name="id" value="<?php echo $user->id; ?>" />
                                
																<label class="control-label" for="theme"><?php echo $this->_labels['theme']; ?></label>
                                <?php echo prosys\admin\view\View::makeArraySelect(UserEntity::$_THEMES, $user->theme, 'theme', 'theme'); ?>
                                
                                <label class="control-label" for="countItemPerPage"><?php echo $this->_labels['countItemPerPage']; ?></label>
                                <input type="number" id="countItemPerPage" name="countItemPerPage" value="<?php echo (($_LOGGED_USER->countItemPerPage) ? $_LOGGED_USER->countItemPerPage : Settings::ITEMS_PER_PAGE); ?>" class="span1 m-wrap" min="1" max="50" />
                                
																<div class="submit-btn">
																	<button type="submit" class="btn green" title="Změnit heslo"><i class="icon-ok"></i> Aplikovat</button>
																	<button type="reset" class="btn red" title="Zrušit změny"><i class="icon-remove"></i> Zrušit</button>
																</div>
															</form>
														</div>
													</div>
                          <div id="tab_2-2" class="tab-pane">
														<div style="height: auto;" id="accordion2-2" class="accordion collapse">
															<form action="#">
																<p>Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod.</p>
																<br />
																<div class="controls">
																	<div class="thumbnail" style="width: 291px; height: 170px;">
																		<img src="http://www.placehold.it/291x170/EFEFEF/AAAAAA&amp;text=no+image" alt="" />
																	</div>
																</div>
																<div class="space10"></div>
																<div class="fileupload fileupload-new" data-provides="fileupload">
																	<div class="input-append">
																		<div class="uneditable-input">
																			<i class="icon-file fileupload-exists"></i> 
																			<span class="fileupload-preview"></span>
																		</div>
																		<span class="btn btn-file">
																		<span class="fileupload-new">Select file</span>
																		<span class="fileupload-exists">Change</span>
																		<input type="file" class="default" />
																		</span>
																		<a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Remove</a>
																	</div>
																</div>
																<div class="clearfix"></div>
																<div class="controls">
																	<span class="label label-important">NOTE!</span>
																	<span>You can write some information here..</span>
																</div>
																<div class="space10"></div>
																<div class="submit-btn">
																	<a href="#" class="btn green">Submit</a>
																	<a href="#" class="btn">Cancel</a>
																</div>
															</form>
														</div>
													</div>
												</div>
											</div>
											<!--end span9--> 
                      
										</div>
									</div>
								</div>
								<!--end tab-pane-->
							</div>
						</div>
						<!--END TABS-->
					</div>
				</div>
				<!-- END PAGE CONTENT-->
			</div>

      <script>
        $(document).ready(function() {
          $('form.user_information').submit(function() {
            $(this).find('input[name="back_to"]').val(document.location.hash);
          });
        });
      </script>

