// Dashboard shells take the same props and read navigation from
// lib/navigation.ts, so another vite-dashboard shell can replace this one in
// layouts/app-layout.tsx.
//
//   RailShell – sidebar-09, ChatGPT-style: rail icons pick a section (they don't
//               navigate); the panel lists its pages and holds the collapse button
export { RailShell } from './rail-shell/rail-shell';
export type { ShellProps } from './shared/shell-types';
