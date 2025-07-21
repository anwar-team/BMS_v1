
// remember to use module.exports instead of tailwind.config in production
tailwind.config = 
   {
      // Note: config only includes the used styles & variables of your selection
      content: ["./src/**/*.{html,vue,svelte,js,ts,jsx,tsx}"],
      theme: {
        extend: {
          fontFamily: {
            'typography-caption-font-family': "Roboto-Regular, sans-serif",
'fontfamily': "Roboto-Regular, sans-serif",
          },
          fontSize: {
            'typography-caption-font-size': "12px",
'fontsize-0875rem': "14px",
'fontsize-075rem': "12px",
          },
          fontWeight: {
            'typography-caption-font-weight': "400",
'fontweightregular': "400",
          },
          lineHeight: {
            'typography-caption-line-height': "166%", 
          },
          letterSpacing: {
             
          },
          borderRadius: {
              
          },
          colors: {
            'bg': '#ffffff',
'library-clickablelayer': 'rgba(0, 0, 0, 0.00)',
'neutral-dark-6': '#686877',
'neutral-dark-1': '#0f0f0f',
'neutral-line': '#e8e8e9',
            'background-paper-elevation-1': '#ffffff',
'divider': 'rgba(0, 0, 0, 0.12)',
'text-primary': 'rgba(0, 0, 0, 0.87)',
'text-secondary': 'rgba(0, 0, 0, 0.60)',
          },
          spacing: {
              
          },
          width: {
             
          },
          minWidth: {
             
          },
          maxWidth: {
             
          },
          height: {
             
          },
          minHeight: {
             
          },
          maxHeight: {
             
          }
        }
      }
    }

          