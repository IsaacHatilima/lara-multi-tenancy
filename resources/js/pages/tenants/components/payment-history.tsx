import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Empty,
    EmptyDescription,
    EmptyHeader,
    EmptyTitle,
} from '@/components/ui/empty';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { EDGE_CELL_PADDING } from '@/lib/table-classes';
import { formatDate } from '../format';
import type { Payment } from '../types';
import { PaymentStatusBadge } from './status-badges';

export function PaymentHistory({ payments }: { payments: Payment[] }) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    <h2>Payment history</h2>
                </CardTitle>
                <CardDescription>
                    The company's most recent charges, newest first.
                </CardDescription>
            </CardHeader>
            <CardContent className="px-0">
                {payments.length === 0 ? (
                    <Empty className="border-t">
                        <EmptyHeader>
                            <EmptyTitle>No payments yet</EmptyTitle>
                            <EmptyDescription>
                                Charges appear here once the company is billed.
                            </EmptyDescription>
                        </EmptyHeader>
                    </Empty>
                ) : (
                    <Table className={EDGE_CELL_PADDING}>
                        <TableHeader className="border-t">
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>Invoice</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">
                                    Amount
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {payments.map((payment) => (
                                <TableRow key={payment.reference}>
                                    <TableCell className="tabular-nums">
                                        <time dateTime={payment.attemptedAt}>
                                            {formatDate(payment.attemptedAt)}
                                        </time>
                                    </TableCell>
                                    <TableCell>
                                        <span className="block font-mono text-xs">
                                            {payment.reference}
                                        </span>
                                        <span className="block text-muted-foreground">
                                            {payment.description}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <PaymentStatusBadge payment={payment} />
                                    </TableCell>
                                    <TableCell className="text-right tabular-nums">
                                        {payment.amount}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                )}
            </CardContent>
        </Card>
    );
}
