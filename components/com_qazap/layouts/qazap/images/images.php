<?php
/**
 * @package     Qazap.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2014 VirtuePlanet Services LLP. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

// Store the data in a local variable before display
$images = $displayData;
$count = count($images);
$galley_rel = $this->options->get('gallery_rel');
$class = (string) $this->options->get('class', '');
$addCloser = false;
?>
<?php if((isset($images[0]->no_image) && $count == 1) || !$this->options->get('cloud_zoom')) : ?>
	<img class="<?php echo $class ? $class : 'medium-thumb-image' ?>" src="<?php echo $images[0]->medium_url ?>" alt="<?php echo $images[0]->name ?>" />
<?php else : ?>
	<div class="qazap-image-wrap">
		<div class="large-image-container" >			
			<a href="<?php echo $images[0]->url ?>" class="cloud-zoom" id="zoom1" rel="adjustX: 10, adjustY:-4">
				<img class="<?php echo $class ? $class : 'large-image' ?>" src="<?php echo $images[0]->medium_url ?>" alt="<?php echo $images[0]->name ?>" />
			</a>		
		</div>
		<?php $addCloser = true; ?>
<?php endif; ?>

<?php if($count > 1 && $this->options->get('gallery')) : ?>
	<div class="row-fluid">
		<div class="span12 thumbnails-container">
			<div class="image-thumb-carousel">
				<ul class="image-thumbnails">
				<?php 
				$i=0;
				foreach($images as $image) : ?>
				<li class="thumbs">
					<a href="<?php echo $image->url ?>" class="cloud-zoom-gallery <?php echo $i==0 ? 'active' : ''; ?>" rel="useZoom: 'zoom1', smallImage: '<?php echo addslashes($image->medium_url) ?>'">
						<img class="img-polaroid" src="<?php echo $image->thumbnail_url ?>" alt="<?php echo $image->name ?>" />
					</a>
					<?php if($this->options->get('modal', 1)) : ?>
						<a href="<?php echo $image->url ?>" class="cloud-zoom-fancybox <?php echo $i==0 ? 'active' : ''; ?>" rel="<?php echo $galley_rel ?>"></a>
					<?php endif; ?>
				</li>
				<?php 
				$i++;
				endforeach; ?>	
				</ul>
			</div>	
		</div>
	</div>	
<?php endif; ?>
	
<?php if($addCloser) : ?>
	</div>
<?php endif; ?>
