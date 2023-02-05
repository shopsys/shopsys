const em = (value) => value / 16 + 'em';

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./pages/**/*.{js,ts,jsx,tsx}",
    "./components/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    screens: {
      xs: em(320),
      maxSm: { max: em(479) },
      sm: em(480),
      maxMd: { max: em(599) },
      md: em(600),
      maxLg: { max: em(768) },
      lg: em(769),
      maxVl: { max: em(1023) },
      vl: em(1024),
      maxXl: { max: em(1239) },
      xl: em(1240),
      maxXxl: { max: em(1559) },
      xxl: em(1560),
    },
    colors: {
      base: '#0d0d0d',
      primary: '#4c5bfd',
      primaryLight: '#a3abff',
      white: '#fff',
      whitesmoke: '#e8e8ea',
      black: '#000',
      orange: '#ecb200',
      orangeLight: '#fff0c4',
      border: '#c4c9ff',
      red: '#ec5353',
      redLight: '#f2a2a2',
      green: '#00ecb1',
      greenLight: '#81f7da',
      greenVeryLight: '#e7fce6',
      greenDark: '#22b92a',
      grey: '#555764',
      greyLight: '#a4a7c1',
      greyVeryLight: '#f5f5f6',
      greyDark: '#414353',
      greyLighter: '#e2e3eb',
      blueLight: '#f2f2ff',
      blue: '#d9d9ff',
      creamWhite: '#fefefe',
      inStock: '#01946f',
    },
    fontFamily: {
      base: ['DM Sans', 'Arial', 'Helvetica', 'sans-serif'],
    },
    fontSize: {
      bigger: '18px',
      default: '16px',
      small: '14px',
      extraSmall: '12px',
    },
    lineHeight: {
      default: 1.3,
    },
    plugins: [],
  }
}
