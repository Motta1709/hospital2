---
name: PharmaCRM Clinical Design System
colors:
  primary: '#00A896' # Verde Esmeralda Vital (Action/Branding)
  on-primary: '#F0F4F8' # Blanco Quirúrgico
  secondary: '#102A43' # Azul Medianoche (Navbar/Titles)
  on-secondary: '#F0F4F8'
  tertiary: '#02C39A' # Menta Curativo (Success/Vitality)
  on-tertiary: '#102A43'
  error: '#FF9F1C' # Naranja Energético (Warning/Low Stock)
  on-error: '#102A43'
  background: '#F0F4F8' # Blanco Quirúrgico (Main background)
  on-background: '#102A43'
  surface: '#FFFFFF' # White surface for cards
  on-surface: '#102A43'
  surface-variant: '#E1E8ED' # Cian Suave (Card backgrounds/Dividers)
  on-surface-variant: '#486581' # Gris Acero (Secondary icons/labels)
  outline: '#E1E8ED' # Cian Suave (Borders)
  outline-variant: '#486581'
typography:
  headline-lg:
    fontFamily: Outfit
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    color: '#102A43'
  headline-md:
    fontFamily: Outfit
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    color: '#102A43'
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
    color: '#102A43'
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
    color: '#486581' # Gris Acero for secondary text
rounded:
  sm: 4px
  DEFAULT: 8px
  lg: 16px
---

# PharmaCRM Clinical Design System

Este sistema de diseño está optimizado para la eficiencia clínica y la claridad operativa. Utiliza una paleta inspirada en entornos médicos modernos, priorizando la legibilidad y eliminando el desorden visual.

## Principios de Refactorización

1. **Claridad y Contraste:** 
   - Se utiliza **Azul Medianoche (#102A43)** para toda la tipografía de alta jerarquía sobre el fondo **Blanco Quirúrgico (#F0F4F8)**, asegurando un contraste AAA.
   - Los botones de acción principal usan **Verde Esmeralda Vital (#00A896)** con texto blanco para máxima visibilidad.

2. **Estructura Visual Simplificada:**
   - Reducción de la superposición de contenedores. Se prefieren sombras sutiles y bordes en **Cian Suave (#E1E8ED)** en lugar de múltiples capas de fondos oscuros.
   - Uso de **Gris Acero (#486581)** para elementos secundarios (menús laterales e iconos), manteniendo el foco en el contenido principal.

3. **Jerarquía Cromática Funcional:**
   - **Menta Curativo (#02C39A):** Estados positivos y éxito.
   - **Naranja Energético (#FF9F1C):** Alertas y atención inmediata.

## Componentes Clave

- **Barras de Navegación:** Fondo **Azul Medianoche** con elementos en **Blanco Quirúrgico**.
- **Tarjetas (Cards):** Fondo blanco con bordes **Cian Suave** y títulos en **Azul Medianoche**.
- **Botones:** 
  - Primarios: **Verde Esmeralda**.
  - Alertas: **Naranja Energético**.
  - Secundarios: **Gris Acero**.
