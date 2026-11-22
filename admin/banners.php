<?php
/** Hero banner create / update / delete actions. */

require_once __DIR__ . '/includes/auth.php';
require_post();
csrf_verify();

ensure_banner_size_columns();

$action = post_string('action');

if ($action === 'delete') {
    $id = post_int('id');
    $stmt = db()->prepare('DELETE FROM banners WHERE id = ?');
    $stmt->execute([$id]);
    flash('success', 'Banner deleted.');
    redirect('admin/index.php?tab=banners');
}

if ($action === 'save') {
    $id       = post_int('id');
    $title    = post_string('title');
    $subtitle = post_string('subtitle');
    $category = post_string('category');
    $badge    = post_string('badge');
    $link     = post_string('link', '#catalog-section');
    $sort     = post_int('sort_order');
    $active   = isset($_POST['is_active']) ? 1 : 0;
    $imageUrl = post_string('image_url');
    $imageMobileUrl = post_string('image_mobile_url');
    $videoUrl = post_string('video_url');
    $imageWidth  = min(4000, max(300, post_int('image_width', 1600)));
    $imageHeight = min(2000, max(100, post_int('image_height', 400)));

    if (mb_strlen($title) < 2) {
        flash('error', 'Banner title is required.');
        redirect('admin/index.php?tab=banners' . ($id > 0 ? '&edit=' . $id : ''));
    }

    $existingImage = '';
    $existingMobile = '';
    $existingVideo = '';
    if ($id > 0) {
        $stmt = db()->prepare('SELECT image, image_mobile, video FROM banners WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch() ?: [];
        $existingImage = (string) ($row['image'] ?? '');
        $existingMobile = (string) ($row['image_mobile'] ?? '');
        $existingVideo = (string) ($row['video'] ?? '');
    }

    $image = $imageUrl !== '' ? $imageUrl : $existingImage;
    $imageMobile = $imageMobileUrl !== '' ? $imageMobileUrl : $existingMobile;
    $video = $videoUrl !== '' ? $videoUrl : $existingVideo;

    if (!empty($_FILES['image_file']['name'])) {
        [$ok, $result] = upload_image($_FILES['image_file'], 'banners', 'banner');
        if ($ok) {
            $image = $result;
        } else {
            flash('error', 'Desktop image upload failed: ' . $result);
            redirect('admin/index.php?tab=banners' . ($id > 0 ? '&edit=' . $id : ''));
        }
    }
    if (!empty($_FILES['image_mobile_file']['name'])) {
        [$ok, $result] = upload_image($_FILES['image_mobile_file'], 'banners', 'banner-mobile');
        if ($ok) {
            $imageMobile = $result;
        } else {
            flash('error', 'Mobile image upload failed: ' . $result);
            redirect('admin/index.php?tab=banners' . ($id > 0 ? '&edit=' . $id : ''));
        }
    }
    if (!empty($_FILES['video_file']['name'])) {
        [$ok, $result] = upload_video($_FILES['video_file'], 'banners', 'banner-video');
        if ($ok) {
            $video = $result;
        } else {
            flash('error', 'Video upload failed: ' . $result);
            redirect('admin/index.php?tab=banners' . ($id > 0 ? '&edit=' . $id : ''));
        }
    }

    if ($link === '') {
        $link = '#catalog-section';
    }

    if ($id > 0) {
        $stmt = db()->prepare(
            'UPDATE banners SET title = ?, subtitle = ?, image = ?, image_mobile = ?, video = ?, image_width = ?, image_height = ?, category = ?, badge = ?, link = ?, sort_order = ?, is_active = ? WHERE id = ?'
        );
        $stmt->execute([$title, $subtitle ?: null, $image, $imageMobile ?: null, $video ?: null, $imageWidth, $imageHeight, $category ?: null, $badge ?: null, $link, $sort, $active, $id]);
        flash('success', 'Banner updated.');
    } else {
        $stmt = db()->prepare(
            'INSERT INTO banners (title, subtitle, image, image_mobile, video, image_width, image_height, category, badge, link, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$title, $subtitle ?: null, $image, $imageMobile ?: null, $video ?: null, $imageWidth, $imageHeight, $category ?: null, $badge ?: null, $link, $sort, $active]);
        flash('success', 'Banner added.');
    }

    redirect('admin/index.php?tab=banners');
}

flash('error', 'Unknown banner action.');
redirect('admin/index.php?tab=banners');
