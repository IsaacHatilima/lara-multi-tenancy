/** For a table that runs edge to edge in a card: its outer cells take the card's padding. */
export const EDGE_CELL_PADDING =
    '[&_td:first-child]:pl-(--card-spacing) [&_th:first-child]:pl-(--card-spacing) [&_td:last-child]:pr-(--card-spacing) [&_th:last-child]:pr-(--card-spacing)';

/**
 * A first column that stays put while the table scrolls sideways. It needs an
 * opaque background, so on row hover it mixes the row's `bg-muted/50` into the
 * card colour itself instead of covering the highlight. It fades with the
 * row's `transition-colors`, or its edge shows while the row catches up.
 */
export const STICKY_FIRST_CELL =
    'sticky left-0 bg-card transition-colors in-[tr:hover]:bg-[color-mix(in_oklab,var(--color-muted)_50%,var(--color-card))]';
