<?php $this->load->view('Layouts/header'); ?>
<section class="right-side">
    <div class="container-fluid">
        <div class="row">
            <?php $i = 0;
            foreach ($menusList->result() as $menu) :
                $user_id = $_SESSION['isUserSession']['user_id'];
                $labels = $_SESSION['isUserSession']['labels'];
                $menu_id = $menu->id;
                if ((!in_array($user_id, []) && !in_array($labels, ['AU', 'CR3', 'CA'])) && in_array($menu_id, [1, 18])) {
                    //continue;
                }?>
            <div class="col-md-2 col-sm-6 col-xs-6 col-md-2-me">
                <a href="<?= base_url($menu->route_link . "/" . $menu->stage) ?>">
                    <!--<div class="lead-box text-center dashboardBox" style="background:<?= $menu->box_bg_color ?>">-->
                    <div class="lead-box text-center dashboardBox">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="text-center serviceBox">
                                    <div class="service-icon orange">
                                        <div class="front-content service-icon mb-3">
                                            <i class="<?= !empty($menu->icon)? $menu->icon :'fa fa-cog'; ?>"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4"><span class="bookmark-title"><?= ucwords(strtolower($menu->menu_name)); ?></span></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
                <?php $i++;
            endforeach; 
            ?>
        </div>
    </div>
</section>
<?php $this->load->view('Layouts/footer') ?>
