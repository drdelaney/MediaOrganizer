<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title><?=$title?></title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.2.0/css/all.css" />
    <!-- Google Fonts Roboto -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" />
    <!-- MDB -->
    <link rel="stylesheet" href="/css/mdb.<?php echo getenv('DARK_THEME') === true ? 'dark.' : '' ?>min.css" />
    <!-- Custom styles -->
    <link rel="stylesheet" href="/css/admin.css" />
</head>

<body>
<!--Main Navigation-->
<header>
    <!-- Sidebar -->
    <nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse bg-white">
    <div class="position-sticky">
            <?php $activeNav = $activeNav ?? ''; ?>
            <div class="list-group list-group-flush mx-3 mt-4">
                <a href="#" class="list-group-item list-group-item-action py-2 ripple"<?=$activeNav == 'main' ? ' active' : ''?>">
                    <i class="fas fa-tachometer-alt fa-fw me-3"></i><span>Main dashboard</span></a>
                <a href="/media" class="list-group-item list-group-item-action py-2 ripple<?=$activeNav == 'media' ? ' active' : ''?>">
                    <i class="fas fa-chart-area fa-fw me-3"></i><span>Media</span></a>
                <a href="#" class="list-group-item list-group-item-action py-2 ripple"<?=$activeNav == 'rent' ? ' active' : ''?>">
                    <i class="fas fa-circle-up me-3"></i><span>Rent List</span></a>
                <a href="#" class="list-group-item list-group-item-action py-2 ripple"<?=$activeNav == 'renters' ? ' active' : ''?>">
                    <i class="fas fa-users me-3"></i><span>Renters</span></a>
            </div>
        </div>
    </nav>
    <!-- Sidebar -->

    <!-- Navbar -->
    <nav id="main-navbar" class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
    <!-- Container wrapper -->
        <div class="container-fluid">
            <!-- Toggle button -->
            <button class="navbar-toggler" type="button" data-mdb-toggle="collapse" data-mdb-target="#sidebarMenu"
                    aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Brand -->
            <a class="navbar-brand" href="#">Media Organizer</a>
            <!-- Right links -->
            <ul class="navbar-nav ms-auto d-flex flex-row">
                <!-- Notification dropdown -->
                <a href="#">Log Out</a>
            </ul>
        </div>
        <!-- Container wrapper -->
    </nav>
    <!-- Navbar -->
</header>
<!--Main layout-->
<main style="margin-top: 58px">
    <div class="container pt-4">
