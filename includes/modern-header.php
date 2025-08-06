<?php
// Include our Tailwind configuration if it exists
if (file_exists(dirname(__DIR__) . '/tailwind-config.php')) {
    require_once dirname(__DIR__) . '/tailwind-config.php';
} else {
    // Fallback function if tailwind-config.php doesn't exist
    function include_tailwind_styles() {
        echo '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">';
        echo '<link href="https://fonts.googleapis.com/css2?family=Onest:wght@400;500;600;700&display=swap" rel="stylesheet">';
    }
    
    function include_dark_mode_script() {
        // Empty function as fallback
    }
}
?>

<!-- Meta tags -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Include Tailwind CSS and fonts -->
<?php include_tailwind_styles(); ?>

<!-- Add brand colors CSS -->
<link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']) == '/' ? '' : dirname($_SERVER['PHP_SELF']); ?>/styles/brand-colors.css">

<!-- Add Tailwind CDN for development (remove in production and use a build process) -->
<script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>

<!-- Use the tailwind.config.js file -->
<script>
    tailwind.config = {
        darkMode: ['class'],
        theme: {
            container: {
                center: true,
                padding: "2rem",
                screens: {
                    "2xl": "1400px",
                },
            },
            extend: {
                fontFamily: {
                    onest: ['Onest', 'sans-serif'],
                    inter: ['Inter', 'sans-serif'],
                    sans: ['Inter', 'sans-serif']
                },
                colors: {
                    border: "hsl(var(--border))",
                    input: "hsl(var(--input))",
                    ring: "hsl(var(--ring))",
                    background: "hsl(var(--background))",
                    foreground: "hsl(var(--foreground))",
                    primary: {
                        DEFAULT: "hsl(var(--primary))",
                        foreground: "hsl(var(--primary-foreground))",
                    },
                    secondary: {
                        DEFAULT: "hsl(var(--secondary))",
                        foreground: "hsl(var(--secondary-foreground))",
                    },
                    mint: {
                        DEFAULT: "hsl(var(--mint))",
                        foreground: "hsl(var(--mint-foreground))",
                    },
                    notebook: {
                        DEFAULT: "hsl(var(--notebook))",
                        foreground: "hsl(var(--notebook-foreground))",
                    },
                    navy: {
                        DEFAULT: "hsl(var(--navy))",
                        foreground: "hsl(var(--navy-foreground))",
                    },
                    destructive: {
                        DEFAULT: "hsl(var(--destructive))",
                        foreground: "hsl(var(--destructive-foreground))",
                    },
                    muted: {
                        DEFAULT: "hsl(var(--muted))",
                        foreground: "hsl(var(--muted-foreground))",
                    },
                    accent: {
                        DEFAULT: "hsl(var(--accent))",
                        foreground: "hsl(var(--accent-foreground))",
                    },
                    popover: {
                        DEFAULT: "hsl(var(--popover))",
                        foreground: "hsl(var(--popover-foreground))",
                    },
                    card: {
                        DEFAULT: "hsl(var(--card))",
                        foreground: "hsl(var(--card-foreground))",
                    },
                },
                borderRadius: {
                    lg: "var(--radius)",
                    md: "calc(var(--radius) - 2px)",
                    sm: "calc(var(--radius) - 4px)",
                },
                keyframes: {
                    "accordion-down": {
                        from: { height: "0" },
                        to: { height: "var(--radix-accordion-content-height)" },
                    },
                    "accordion-up": {
                        from: { height: "var(--radix-accordion-content-height)" },
                        to: { height: "0" },
                    },
                },
                animation: {
                    "accordion-down": "accordion-down 0.2s ease-out",
                    "accordion-up": "accordion-up 0.2s ease-out",
                }
            }
        }
    }
    };
</script>