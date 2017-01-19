<aside class="main-sidebar" bs-navbar>

    <section class="sidebar">

        <!-- search form -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>
        <!-- /.search form -->

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                    ['label' => 'Menu Yii2', 'options' => ['class' => 'header']],
                    ['label' => 'Gii', 'icon' => 'fa fa-file-code-o', 'url' => ['/admin'], 'options' => ['data-match-route' => '/$']],
                    ['label' => 'Debug', 'icon' => 'fa fa-dashboard', 'url' => ['/admin/debug'], 'options' => ['data-match-route' => '/admin/debug']],
                    ['label' => 'Pagination Demo', 'url' => ['/admin/pagination-demo'], 'visible' => Yii::$app->user->isGuest],
                    [
                        'label' => 'Blog',
                        'icon' => 'fa fa-pencil-square',
                        'url' => '#',
                        'items' => [
                            ['label' => 'Blog', 'icon' => 'fa fa-pencil-square-o', 'url' => ['/admin/blog'],],
                            ['label' => 'Category', 'icon' => 'fa fa-list-ol', 'url' => ['/admin/category'],],
                            [
                                'label' => 'Level One',
                                'icon' => 'fa fa-circle-o',
                                'url' => '#',
                                'items' => [
                                    ['label' => 'Level Two', 'icon' => 'fa fa-circle-o', 'url' => '#',],
                                    [
                                        'label' => 'Level Two',
                                        'icon' => 'fa fa-circle-o',
                                        'url' => '#',
                                        'items' => [
                                            ['label' => 'Level Three', 'icon' => 'fa fa-circle-o', 'url' => '#',],
                                            ['label' => 'Level Three', 'icon' => 'fa fa-circle-o', 'url' => '#',],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ) ?>

    </section>

</aside>
