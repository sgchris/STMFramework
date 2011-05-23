<div class='left_menu_panel'>
	<div style='background:#E3E3E3;color:#777;text-align:center;font-size:18px;height:30px;line-height:30px;border-bottom:1px solid #AAA'>Main Menu</div>
	<div class='left_menu'>
            <?php
            if (!empty($left_menu)) {
                foreach ($left_menu as $idx=>$menu) {
                    if (is_array($menu)) {
                        $md5                = md5($idx);
                        $display_css_value  = preg_match('%^'.preg_quote($idx).'%smi', $_GET['page']) ? 'block' : 'none';
                        echo '<div class="left_menu">';
                            echo '<a href="javascript:;" onclick="$(\'#'.$md5.'\').slideToggle();">'.$idx.' &raquo;</a>';
                            echo '<div id="'.$md5.'" class="left_menu sub" style="display: '.$display_css_value.';">';
                            foreach ($menu as $sub_idx=>$sub_menu) {
                                echo '<div class="sub_menu_toggled">';
                                echo '<a href="'.$sub_menu.'">'.$sub_idx.'</a>';
                                echo '</div>';

                            }
                            echo '</div>';
                        echo '</div>';
                    } else {
                        echo '<div class="left_menu"><a href="'.$menu.'">'.$idx.'</a></div>';
                    }
                }
            }
            ?>
	</div>
</div>
