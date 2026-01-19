import { Link, usePage } from '@inertiajs/react';
import {
    Menu,
    Search,
    X,
    Phone,
    Mail,
    Clock,
} from 'lucide-react';
import { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { type SharedData } from '@/types';

import AppearanceToggleDropdown from './appearance-dropdown';
import CourtLogo from './court-logo';
import NavigationMenu from './menu/NavigationMenu';
import MobileMenu from './menu/MobileMenu';

export default function Header() {
    const page = usePage<SharedData>();
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    const headerMenu = page.props.menus?.header || [];
    const mobileMenu = page.props.menus?.mobile || headerMenu;

    return (
        <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            {/* Top bar with contact info */}
            <div className="hidden bg-primary text-primary-foreground py-2 px-4 md:block">
                <div className="mx-auto flex max-w-7xl items-center justify-between text-sm">
                    <div className="flex items-center gap-6">
                        <span className="flex items-center gap-2">
                            <Clock className="h-4 w-4" />
                            <span>Senin - Kamis: 07:30 - 16:00, Jumat: 07:30 - 11:30</span>
                        </span>
                    </div>
                    <div className="flex items-center gap-4">
                        <span className="flex items-center gap-2">
                            <Phone className="h-4 w-4" />
                            <span>(0541) 2901234</span>
                        </span>
                        <span className="flex items-center gap-2">
                            <Mail className="h-4 w-4" />
                            <span>pa.penajam@gmail.com</span>
                        </span>
                    </div>
                </div>
            </div>

            {/* Main header */}
            <div className="mx-auto flex h-16 items-center justify-between px-4 md:max-w-7xl">
                {/* Logo */}
                <div className="flex items-center">
                    <CourtLogo />
                </div>

                {/* Desktop Navigation */}
                <NavigationMenu items={headerMenu} className="hidden lg:flex" />

                {/* Right side actions */}
                <div className="flex items-center gap-2">
                    <motion.div whileHover={{ scale: 1.1 }} whileTap={{ scale: 0.9 }}>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="hidden h-9 w-9 rounded-md sm:flex hover:bg-accent"
                        >
                            <Search className="h-5 w-5" />
                            <span className="sr-only">Search</span>
                        </Button>
                    </motion.div>
                    <AppearanceToggleDropdown className="hidden sm:flex" />
                    <Sheet open={mobileMenuOpen} onOpenChange={setMobileMenuOpen}>
                        <SheetTrigger asChild>
                            <motion.div whileHover={{ scale: 1.1 }} whileTap={{ scale: 0.9 }}>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="lg:hidden h-9 w-9 rounded-md"
                                >
                                    <AnimatePresence mode="wait">
                                        {mobileMenuOpen ? (
                                            <motion.div
                                                key="close"
                                                initial={{ rotate: -90, opacity: 0 }}
                                                animate={{ rotate: 0, opacity: 1 }}
                                                exit={{ rotate: 90, opacity: 0 }}
                                                transition={{ duration: 0.2 }}
                                            >
                                                <X className="h-5 w-5" />
                                            </motion.div>
                                        ) : (
                                            <motion.div
                                                key="menu"
                                                initial={{ rotate: 90, opacity: 0 }}
                                                animate={{ rotate: 0, opacity: 1 }}
                                                exit={{ rotate: -90, opacity: 0 }}
                                                transition={{ duration: 0.2 }}
                                            >
                                                <Menu className="h-5 w-5" />
                                            </motion.div>
                                        )}
                                    </AnimatePresence>
                                    <span className="sr-only">Toggle menu</span>
                                </Button>
                            </motion.div>
                        </SheetTrigger>
                        <SheetContent side="right" className="flex flex-col">
                            <SheetHeader className="flex flex-row items-center justify-between pb-4">
                                <SheetTitle className="sr-only">
                                    Navigation Menu
                                </SheetTitle>
                                <div className="flex items-center gap-2">
                                    <CourtLogo />
                                </div>
                            </SheetHeader>

                            {/* Mobile Navigation */}
                            <MobileMenu
                                items={mobileMenu}
                                onItemClick={() => setMobileMenuOpen(false)}
                                className="mt-4"
                            />

                            {/* Mobile footer with contact info */}
                            <div className="border-t pt-4">
                                <div className="space-y-2 text-sm text-muted-foreground">
                                    <div className="flex items-center gap-2">
                                        <Phone className="h-4 w-4" />
                                        <span>(0541) 2901234</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Mail className="h-4 w-4" />
                                        <span>pa.penajam@gmail.com</span>
                                    </div>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>
        </header>
    );
}
