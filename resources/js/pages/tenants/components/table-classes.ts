/** For a table that runs edge to edge in a card: its outer cells take the card's padding. */
export const EDGE_CELL_PADDING =
    '[&_td:first-child]:pl-(--card-spacing) [&_th:first-child]:pl-(--card-spacing) [&_td:last-child]:pr-(--card-spacing) [&_th:last-child]:pr-(--card-spacing)';
