const dateFormat = new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' });

/** An ISO 8601 date from a Resource, in the viewer's locale. */
export function formatDate(iso: string): string {
    return dateFormat.format(new Date(iso));
}
