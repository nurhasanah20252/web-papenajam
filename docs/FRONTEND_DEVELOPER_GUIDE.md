# Frontend Developer Guide - PA Penajam Website

**Quick Reference for PH1.4 Frontend Setup**

---

## Quick Start

### Development Server
```bash
# Start Vite dev server
npm run dev

# Build for production
npm run build

# Type checking
npm run types

# Linting
npm run lint

# Format code
npm run format
```

### File Structure
```
resources/js/
├── pages/           # Create new pages here
├── components/      # Reusable components
├── layouts/         # Page layouts
├── hooks/           # Custom React hooks
└── lib/             # Utility functions
```

---

## Creating New Pages

### 1. Create Page Component

**File:** `resources/js/pages/my-page.tsx`

```typescript
import { Head } from '@inertiajs/react';
import MainLayout from '@/layouts/main-layout';
import PageContainer from '@/components/page-container';
import PageHeader from '@/components/page-header';

export default function MyPage() {
    return (
        <MainLayout>
            <Head title="My Page" />

            <PageContainer>
                <PageHeader
                    title="Page Title"
                    description="Page description"
                />

                {/* Your content here */}
            </PageContainer>
        </MainLayout>
    );
}
```

### 2. Create Backend Route

**File:** `routes/web.php`

```php
use Inertia\Inertia;

Route::get('/my-page', function () {
    return Inertia::render('MyPage');
});
```

---

## Using Layouts

### MainLayout (Public Pages)

```typescript
import MainLayout from '@/layouts/main-layout';

<MainLayout
    title="Page Title"
    breadcrumbs={[
        { title: 'Home', href: '/' },
        { title: 'Current', href: '/current' },
    ]}
>
    {children}
</MainLayout>
```

### AuthLayout (Authentication)

```typescript
import { AuthLayout } from '@/layouts/auth-layout';

<AuthLayout title="Sign In">
    {children}
</AuthLayout>
```

---

## Using UI Components

### Button

```typescript
import { Button } from '@/components/ui/button';

// Variants
<Button variant="default">Default</Button>
<Button variant="destructive">Destructive</Button>
<Button variant="outline">Outline</Button>
<Button variant="secondary">Secondary</Button>
<Button variant="ghost">Ghost</Button>
<Button variant="link">Link</Button>

// Sizes
<Button size="sm">Small</Button>
<Button size="default">Default</Button>
<Button size="lg">Large</Button>
<Button size="icon"><Icon /></Button>

// With Inertia Link
import { Link } from '@inertiajs/react';

<Button asChild>
    <Link href="/route">Link Button</Link>
</Button>
```

### Card

```typescript
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';

<Card>
    <CardHeader>
        <CardTitle>Card Title</CardTitle>
        <CardDescription>Card description</CardDescription>
    </CardHeader>
    <CardContent>
        <p>Card content</p>
    </CardContent>
    <CardFooter>
        <Button>Action</Button>
    </CardFooter>
</Card>
```

### Input

```typescript
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

<div>
    <Label htmlFor="email">Email</Label>
    <Input
        id="email"
        type="email"
        placeholder="email@example.com"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
    />
</div>
```

### Alert

```typescript
import { Alert } from '@/components/ui/alert';

<Alert variant="destructive">
    Error message
</Alert>

<Alert>
    Success message
</Alert>
```

---

## Theme Support

### Using Theme Hook

```typescript
import { useAppearance } from '@/hooks/use-appearance';

function MyComponent() {
    const { appearance, resolvedAppearance, updateAppearance } = useAppearance();

    return (
        <div>
            <p>Current theme: {appearance}</p>
            <p>Resolved: {resolvedAppearance}</p>
            <button onClick={() => updateAppearance('light')}>
                Light Mode
            </button>
            <button onClick={() => updateAppearance('dark')}>
                Dark Mode
            </button>
        </div>
    );
}
```

### Theme-Aware Components

```typescript
// Use semantic color tokens
className="bg-background text-foreground"
className="bg-primary text-primary-foreground"
className="bg-muted text-muted-foreground"

// Dark mode variants
className="dark:bg-card dark:text-card-foreground"
```

---

## Utility Functions

### Class Name Merger

```typescript
import { cn } from '@/lib/utils';

// Merge Tailwind classes intelligently
<div className={cn(
    'base-class',
    condition && 'conditional-class',
    'another-class',
)} />
```

### Date Formatting

```typescript
import { formatDate } from '@/lib/utils';

const formatted = formatDate(new Date());
// Output: "18 Januari 2026" (Indonesian locale)
```

### URL Helper

```typescript
import { toUrl } from '@/lib/utils';

const url = toUrl('/route'); // string
const url2 = toUrl({ url: '/route', method: 'get' }); // string
```

---

## Custom Hooks

### useScrollReveal

```typescript
import { useScrollReveal } from '@/hooks/use-scroll-reveal';

function MyComponent() {
    const reveal = useScrollReveal({ triggerOnce: true });

    return (
        <div ref={reveal.ref} className="scroll-reveal">
            Content
        </div>
    );
}
```

### useStaggerChildren

```typescript
import { StaggerContainer, StaggerItem } from '@/hooks/use-stagger-children';

<StaggerContainer>
    {items.map((item, index) => (
        <StaggerItem key={item.id} delay={index * 0.1}>
            <Card>{item.content}</Card>
        </StaggerItem>
    ))}
</StaggerContainer>
```

---

## Animations

### Framer Motion

```typescript
import { motion } from 'framer-motion';

<motion.div
    initial={{ opacity: 0, y: 20 }}
    animate={{ opacity: 1, y: 0 }}
    transition={{ duration: 0.5 }}
>
    Content
</motion.div>
```

### Custom Animation Classes

```css
/* Fade in */
<div className="animate-fade-in">Content</div>

/* Slide in top */
<div className="animate-slide-in-top">Content</div>

/* Scale in */
<div className="animate-scale-in">Content</div>

/* Float */
<div className="animate-float">Content</div>

/* Hover lift */
<div className="hover-lift">Content</div>
```

---

## Inertia.js Patterns

### Navigation

```typescript
import { Link } from '@inertiajs/react';
import { router } from '@inertiajs/react';

// Link component
<Link href="/route">Go to route</Link>

// Programmatic navigation
router.visit('/route');
router.visit('/route', { method: 'post', data: { foo: 'bar' } });

// Back/forward
router.back();
router.forward();
```

### Forms (Inertia v2)

```typescript
import { Form } from '@inertiajs/react';

<Form action="/submit" method="post">
    {({ errors, processing, wasSuccessful }) => (
        <>
            <Input name="email" error={errors.email} />
            <Button type="submit" disabled={processing}>
                {processing ? 'Submitting...' : 'Submit'}
            </Button>
            {wasSuccessful && <div>Success!</div>}
        </>
    )}
</Form>
```

### Data Fetching

```typescript
// Server-side (in controller)
return Inertia::render('Page', [
    'users' => User::all(),
]);

// Client-side (in page)
interface PageProps {
    users: User[];
}

export default function Page({ users }: PageProps) {
    // Use users data
}
```

---

## TypeScript Types

### Common Types

```typescript
import type { User, MenuItem, BreadcrumbItem, SharedData } from '@/types';

// User type
const user: User = usePage().props.auth.user;

// Menu item
const menuItem: MenuItem = {
    id: 1,
    title: 'Home',
    url: '/',
    url_type: 'url',
    target_blank: false,
    is_active: true,
};

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: '/' },
    { title: 'Page', href: '/page' },
];
```

### Page Props Pattern

```typescript
interface PageProps {
    auth: {
        user: User;
    };
    [key: string]: unknown;
}

export default function Page({ auth, ...props }: PageProps) {
    // Use auth.user and props
}
```

---

## Responsive Design

### Breakpoints

```typescript
// Tailwind default breakpoints
sm: '640px'   // phones
md: '768px'   // tablets
lg: '1024px'  // laptops
xl: '1280px'  // desktops
```

### Responsive Classes

```typescript
// Mobile-first approach
<div className="w-full md:w-1/2 lg:w-1/3">
    Responsive width
</div>

// Hide/show
<div className="hidden md:block">
    Desktop only
</div>

<div className="block md:hidden">
    Mobile only
</div>
```

---

## Best Practices

### Component Structure

```typescript
// 1. Imports
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

// 2. Types/Interfaces
interface MyComponentProps {
    title: string;
    onSubmit: () => void;
}

// 3. Component
export default function MyComponent({ title, onSubmit }: MyComponentProps) {
    // 4. Hooks
    const [state, setState] = useState();

    // 5. Effects
    useEffect(() => {
        // side effects
    }, []);

    // 6. Handlers
    const handleClick = () => {
        // handle click
    };

    // 7. Render
    return (
        <div>
            {/* JSX */}
        </div>
    );
}
```

### Performance

```typescript
// Lazy load components
const HeavyComponent = lazy(() => import('./HeavyComponent'));

// Memoize expensive computations
const value = useMemo(() => expensiveCalc(data), [data]);

// Memoize callbacks
const callback = useCallback(() => {
    doSomething(dep);
}, [dep]);

// Use Inertia's deferred props for large data
```

### Accessibility

```typescript
// Semantic HTML
<nav>, <main>, <section>, <article>, <header>, <footer>

// ARIA labels
<button aria-label="Close menu">×</button>

// Keyboard navigation
<button onKeyDown={(e) => e.key === 'Enter' && handleClick()}>

// Focus management
<input autoFocus ref={inputRef} />

// Alt text for images
<img src="..." alt="Descriptive text" />
```

---

## Common Tasks

### Add New UI Component

```bash
# Using shadcn CLI
npx shadcn add [component-name]

# Example
npx shadcn add table
npx shadcn add tabs
```

### Create Reusable Component

```typescript
// resources/js/components/my-component.tsx
import { cn } from '@/lib/utils';

interface MyComponentProps {
    children: React.ReactNode;
    className?: string;
}

export default function MyComponent({ children, className }: MyComponentProps) {
    return (
        <div className={cn('base-styles', className)}>
            {children}
        </div>
    );
}
```

### Handle Form Errors

```typescript
import { useForm } from '@inertiajs/react';

const { data, setData, post, processing, errors } = useForm({
    name: '',
    email: '',
});

const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/submit', {
        onError: (errors) => {
            console.error('Validation failed:', errors);
        },
    });
};

<input
    value={data.email}
    onChange={(e) => setData('email', e.target.value)}
    className={errors.email && 'border-destructive'}
/>
{errors.email && <p className="text-destructive">{errors.email}</p>}
```

---

## Troubleshooting

### Build Issues

```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Clear Vite cache
rm -rf node_modules/.vite
npm run dev

# Type check
npm run types
```

### Hot Module Replacement Not Working

```bash
# Restart dev server
# Check vite.config.ts refresh: true
# Clear browser cache
```

### Styles Not Applying

```bash
# Check Tailwind CSS imports
# Ensure @import "tailwindcss" is in app.css
# Check @source paths in app.css
# Run npm run build to verify
```

---

## Resources

### Documentation
- [Inertia.js v2 Docs](https://inertiajs.com/)
- [React 19 Docs](https://react.dev/)
- [Tailwind CSS v4 Docs](https://tailwindcss.com/docs/v4-beta)
- [shadcn/ui Docs](https://ui.shadcn.com/)
- [Framer Motion Docs](https://www.framer.com/motion/)

### Project Files
- Vite Config: `vite.config.ts`
- TypeScript Config: `tsconfig.json`
- Tailwind Config: `resources/css/app.css`
- Components: `resources/js/components/`
- Pages: `resources/js/pages/`

---

**Last Updated:** January 18, 2026
**Phase:** PH1.4 - Basic Frontend Setup
**Status:** ✅ Complete
