import { useRef } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogBody,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

export function DialogsSection() {
    const triggerRef = useRef<HTMLButtonElement>(null);
    const titleRef = useRef<HTMLHeadingElement>(null);
    return (
        <section
            aria-labelledby="dialogs-title"
            className="flex flex-col gap-4"
        >
            <h2 id="dialogs-title" className="text-sm font-medium">
                Dialogs
            </h2>
            <div className="flex flex-wrap items-center gap-4">
                <Dialog stack>
                    <DialogTrigger
                        render={<Button ref={triggerRef} variant="outline" />}
                    >
                        Open dialog
                    </DialogTrigger>
                    <DialogContent
                        className="sm:max-w-lg"
                        initialFocus={titleRef}
                        finalFocus={triggerRef}
                    >
                        <DialogHeader className="pr-8">
                            <DialogTitle
                                ref={titleRef}
                                tabIndex={-1}
                                className="outline-none"
                            >
                                Workspace guide
                            </DialogTitle>
                            <DialogDescription>
                                Component usage for this stacked dialog.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogBody
                            role="region"
                            aria-label="Workspace guide content"
                            tabIndex={0}
                            className="no-scrollbar flex max-h-[50vh] flex-col gap-5"
                        >
                            <code className="block text-xs leading-relaxed wrap-anywhere whitespace-pre-wrap text-muted-foreground">
                                {`<Dialog stack>
  <DialogTrigger render={<Button variant="outline" />}>
    Open dialog
  </DialogTrigger>
  <DialogContent
    className="sm:max-w-lg"
    initialFocus={titleRef}
    finalFocus={triggerRef}
  >
    <DialogHeader>
      <DialogTitle>Workspace guide</DialogTitle>
      <DialogDescription>…</DialogDescription>
    </DialogHeader>
    <DialogBody>…</DialogBody>
    <DialogFooter>
      <DialogClose render={<Button variant="outline" />}>
        Close
      </DialogClose>
    </DialogFooter>
  </DialogContent>
</Dialog>`}
                            </code>
                        </DialogBody>
                        <DialogFooter>
                            <DialogClose render={<Button variant="outline" />}>
                                Close
                            </DialogClose>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </section>
    );
}
