<?php
/**
 * Tailwind CSS Configuration for PHP
 * This file integrates Tailwind CSS with your PHP project
 */

// Define the fonts
$fontOnest = 'https://fonts.googleapis.com/css2?family=Onest:wght@400;500;600;700&display=swap';
$fontInter = 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap';

// Function to include the Tailwind styles and fonts
function include_tailwind_styles() {
    global $fontOnest, $fontInter;
    
    // Add version to prevent caching during development
    $version = '1.0.0';
    
    // Output the font and style links
    echo '<link rel="stylesheet" href="' . $fontOnest . '">';
    echo '<link rel="stylesheet" href="' . $fontInter . '">';
    
    // Include Tailwind CSS (you'll need to compile this with a build process)
    echo '<link rel="stylesheet" href="styles/tailwind.css?v=' . $version . '">';
    
    // Add CSS variables for the fonts
    echo '<style>
        :root {
            --font-onest: "Onest", system-ui, sans-serif;
            --font-inter: "Inter", system-ui, sans-serif;
        }
    </style>';
}

// Function to add the dark mode toggle script
function include_dark_mode_script() {
    echo '<script>
        // Check for dark mode preference
        if (localStorage.theme === "dark" || (!("theme" in localStorage) && 
            window.matchMedia("(prefers-color-scheme: dark)").matches)) {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }
        
        // Function to toggle dark mode
        function toggleDarkMode() {
            if (document.documentElement.classList.contains("dark")) {
                document.documentElement.classList.remove("dark");
                localStorage.theme = "light";
            } else {
                document.documentElement.classList.add("dark");
                localStorage.theme = "dark";
            }
        }
    </script>';
}

// Function to add Tailwind utility classes to common PHP elements
function tailwind_utility_classes() {
    return [
        // Layout
        'container' => 'mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl',
        'section' => 'py-12 md:py-16 lg:py-20',
        
        // Typography
        'heading-1' => 'text-4xl font-bold tracking-tight sm:text-5xl md:text-6xl font-onest',
        'heading-2' => 'text-3xl font-bold tracking-tight sm:text-4xl font-onest',
        'heading-3' => 'text-2xl font-bold tracking-tight sm:text-3xl font-onest',
        'paragraph' => 'text-base text-foreground/80 leading-7',
        
        // Buttons
        'btn-primary' => 'inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary',
        'btn-secondary' => 'inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground hover:bg-secondary/80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary',
        'btn-outline' => 'inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium text-foreground shadow-sm hover:bg-accent hover:text-accent-foreground focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ring',
        
        // Cards
        'card' => 'rounded-lg border bg-card text-card-foreground shadow-sm',
        'card-header' => 'flex flex-col space-y-1.5 p-6',
        'card-title' => 'text-2xl font-semibold leading-none tracking-tight',
        'card-description' => 'text-sm text-muted-foreground',
        'card-content' => 'p-6 pt-0',
        'card-footer' => 'flex items-center p-6 pt-0',
        
        // Forms
        'input' => 'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
        'label' => 'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70',
        
        // Pricing tables
        'pricing-card' => 'flex flex-col p-6 bg-card rounded-lg shadow-sm border border-border transition-all hover:shadow-md',
        'pricing-header' => 'mb-4 text-center',
        'pricing-name' => 'text-xl font-bold',
        'pricing-description' => 'text-sm text-muted-foreground mt-1.5',
        'pricing-price' => 'text-3xl font-bold mt-4',
        'pricing-period' => 'text-sm text-muted-foreground',
        'pricing-features' => 'mt-6 space-y-2',
        'pricing-feature-item' => 'flex items-center text-sm',
        'pricing-cta' => 'mt-6',
    ];
}

// Helper function to get a Tailwind class
function tw($className) {
    $classes = tailwind_utility_classes();
    return isset($classes[$className]) ? $classes[$className] : '';
}

// Helper function to output a Tailwind class
function tw_class($className) {
    echo tw($className);
}
?>
