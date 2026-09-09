<?php
/**
 * People list (instructors / assistants) — image + name cards.
 *
 * @var array  $people List of [ 'name' => string, 'image_id' => int ].
 * @var string $title  Section heading.
 * @var string $modifier Optional BEM modifier suffix.
 * @package WP_Course_Intro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $people ) || ! is_array( $people ) ) {
	return;
}

$modifier = isset( $modifier ) ? sanitize_html_class( $modifier ) : '';
?>
<div class="wci-people-block<?php echo $modifier ? ' wci-people-block--' . esc_attr( $modifier ) : ''; ?>">
	<h3 class="wci-people-block__title"><?php echo esc_html( $title ); ?></h3>
	<ul class="wci-people-block__list">
		<?php foreach ( $people as $person ) : ?>
			<li class="wci-person-card">
				<span class="wci-person-card__avatar">
					<?php
					if ( ! empty( $person['image_id'] ) ) {
						echo wp_get_attachment_image(
							$person['image_id'],
							array( 96, 96 ),
							false,
							array( 'class' => 'wci-person-card__img', 'loading' => 'lazy' )
						);
					} else {
						echo '<span class="wci-person-card__img wci-person-card__img--placeholder" aria-hidden="true">'
							. esc_html( mb_substr( (string) $person['name'], 0, 1 ) )
							. '</span>';
					}
					?>
				</span>
				<span class="wci-person-card__name"><?php echo esc_html( $person['name'] ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
