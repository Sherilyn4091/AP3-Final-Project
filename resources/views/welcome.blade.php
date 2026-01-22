{{-- resources/views/welcome.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Lab - Master Your Musical Journey</title>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .hero {
            background: linear-gradient(
                rgba(0, 0, 0, 0.65),    /* stronger dark overlay top */
                rgba(0, 0, 0, 0.75)     /* even darker at bottom for better readability */
            ), 
            url('https://res.cloudinary.com/dibojpqg2/image/upload/v1769103295/cover_bl3igm.jpg');  /* ← change to your actual filename, e.g. hero-main.jpg */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .hero-content {
            background: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(6px);
        }

        .hero h1 {
            text-shadow: 3px 3px 12px rgba(0,0,0,0.8);
            letter-spacing: -1px;
        }

        .hero p {text-shadow: 1px 1px 6px rgba(0,0,0,0.7);}

        .star {
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s;
        }

        .star:hover, .star.active {color: #d4af7a;}
    </style>
</head>
<body class="text-gray-800 leading-relaxed">

    <header class="bg-white/95 px-[3%] sm:px-[5%] py-2 sm:py-3 md:py-4 fixed w-full top-0 z-[1000] shadow-md flex justify-between items-center">
        <div class="flex items-center gap-2 sm:gap-3">
            <a href="{{ route('home') }}">
                <img 
                    src="https://res.cloudinary.com/dibojpqg2/image/upload/v1766933637/music-lab-logo_1_lfcsqw.png" 
                    alt="Music Lab Logo" 
                    class="h-8 sm:h-10 md:h-12 w-auto" 
                />
            </a>
        </div>

        <div class="flex gap-2 sm:gap-3">
            <a href="/login" class="bg-[#5a6c7d] text-white px-3 sm:px-4 md:px-6 py-1.5 sm:py-2 rounded-md text-xs sm:text-sm transition-colors hover:bg-[#4a5c6d]">Log In</a>
            <a href="/register" class="bg-[#d4af7a] text-gray-800 px-3 sm:px-4 md:px-6 py-1.5 sm:py-2 rounded-md text-xs sm:text-sm font-medium transition-colors hover:bg-[#c49d68]">Register</a>
        </div>
    </header>

    <section class="hero min-h-[300px] sm:min-h-[400px] md:min-h-[500px] h-[60vh] sm:h-[65vh] md:h-[70vh] flex flex-col justify-center items-center text-center text-white px-4 sm:px-6 relative mt-10 sm:mt-12 md:mt-14">
        <div class="hero-content p-4 sm:p-6 md:p-8 rounded-xl sm:rounded-2xl max-w-[90%] sm:max-w-[600px] md:max-w-[800px] shadow-[0_10px_40px_rgba(0,0,0,0.4)]">
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black mb-3 sm:mb-4">Master Your Musical Journey</h1>
            <p class="text-xs sm:text-sm md:text-base lg:text-lg max-w-[700px] mb-4 sm:mb-6 md:mb-8 leading-relaxed opacity-95">Learn from expert instructors, access premium instruments, and unlock your musical potential with our comprehensive online music education platform</p>
            <div class="flex gap-2 sm:gap-3 md:gap-4 flex-wrap justify-center">
                <button class="px-4 sm:px-6 md:px-8 py-2 sm:py-2.5 md:py-3 text-xs sm:text-sm font-semibold rounded-full cursor-pointer transition-all shadow-[0_6px_20px_rgba(0,0,0,0.3)] bg-[#d4af7a] text-gray-800 hover:-translate-y-1 hover:shadow-[0_12px_30px_rgba(0,0,0,0.4)]" onclick="document.getElementById('lessons').scrollIntoView({behavior: 'smooth'})">Start Learning Today</button>
                <button class="px-4 sm:px-6 md:px-8 py-2 sm:py-2.5 md:py-3 text-xs sm:text-sm font-semibold rounded-full cursor-pointer transition-all shadow-[0_6px_20px_rgba(0,0,0,0.3)] bg-white/15 text-white border-2 border-white/40 hover:-translate-y-1 hover:shadow-[0_12px_30px_rgba(0,0,0,0.4)]" onclick="document.getElementById('lessons').scrollIntoView({behavior: 'smooth'})">Explore Courses</button>
            </div>
        </div>
    </section>

    <section class="py-10 sm:py-14 md:py-20 px-[3%] sm:px-[5%] bg-[#343333] text-center text-white">
        <!-- Heading -->
        <h2 class="text-xl sm:text-2xl md:text-3xl font-bold mb-3 sm:mb-4 tracking-wide font-['Poppins']">
            W e l c o m e&nbsp;&nbsp;t o
            <span class="block font-['Pacifico'] text-[#d4af7a] mt-1 sm:mt-2">
                Music Lab
            </span>
        </h2>

        <p class="max-w-[850px] mx-auto mb-8 sm:mb-10 md:mb-14 text-gray-300 text-sm sm:text-base md:text-lg px-4">
            Award-winning excellence in music education and instruments | Trusted by musicians, recognized by Cebu.
        </p>

        <!-- Cards -->
        <div class="w-full overflow-x-auto">
            <div class="min-w-[1050px] grid grid-cols-5 gap-6">

                <!-- Award: 10 Years -->
                <div class="group bg-[#1a1a1a] border border-white/10 rounded-xl p-5 transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_10px_40px_rgba(212,175,122,0.25)]">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-[#d4af7a] flex items-center justify-center group-hover:scale-110 transition">
                        <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6 2a1 1 0 00-1 1v3a5 5 0 0010 0V3a1 1 0 00-1-1H6zM5 9a5 5 0 0010 0v6l-5-3-5 3V9z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-sm tracking-wide font-['Poppins']">
                        10 Years of Service
                    </h3>
                    <p class="text-xs text-gray-400 mt-2">
                        A decade of passion, growth, and dedication to music.
                    </p>
                </div>

                <!-- Award: Best Music Shop -->
                <div class="group bg-[#1a1a1a] border border-white/10 rounded-xl p-5 transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_10px_40px_rgba(212,175,122,0.25)]">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-[#d4af7a] flex items-center justify-center group-hover:rotate-6 transition">
                        <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 3h12l1 4H3l1-4zM3 8h14v9a1 1 0 01-1 1H4a1 1 0 01-1-1V8z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-sm tracking-wide font-['Poppins']">
                        Best Music Shop
                    </h3>
                    <p class="text-xs text-gray-400 mt-2">
                        4× SunStar-recognized excellence in retail.
                    </p>
                </div>

                <!-- Award: Best of Cebu -->
                <div class="group bg-[#1a1a1a] border border-white/10 rounded-xl p-5 transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_10px_40px_rgba(212,175,122,0.25)]">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-[#d4af7a] flex items-center justify-center group-hover:scale-110 transition">
                        <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.974a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.974c.3.921-.755 1.688-1.54 1.118l-3.385-2.46a1 1 0 00-1.176 0l-3.385 2.46c-.784.57-1.838-.197-1.54-1.118l1.287-3.974a1 1 0 00-.364-1.118L2.046 9.4c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.287-3.974z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-sm tracking-wide font-['Poppins']">
                        Best of Cebu
                    </h3>
                    <p class="text-xs text-gray-400 mt-2">
                        Awarded multiple years for excellence.
                    </p>
                </div>

                <!-- Core: Best Instruments -->
                <div class="group bg-[#1a1a1a] border border-white/10 rounded-xl p-5 transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_10px_40px_rgba(212,175,122,0.25)]">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-[#d4af7a] flex items-center justify-center group-hover:rotate-[-6deg] transition">
                        <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-sm tracking-wide font-['Poppins']">
                        Best Instruments
                    </h3>
                    <p class="text-xs text-gray-400 mt-2">
                        Premium instruments trusted by pros.
                    </p>
                </div>

                <!-- Core: Expert Lessons -->
                <div class="group bg-[#1a1a1a] border border-white/10 rounded-xl p-5 transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_10px_40px_rgba(212,175,122,0.25)]">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-[#d4af7a] flex items-center justify-center group-hover:scale-110 transition">
                        <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-sm tracking-wide font-['Poppins']">
                        Expert Lessons
                    </h3>
                    <p class="text-xs text-gray-400 mt-2">
                        Learn from seasoned instructors.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <section id="lessons" class="py-8 sm:py-12 md:py-16 px-[3%] sm:px-[5%] bg-[#adadae]">
        <h2 class="text-center text-xl sm:text-2xl md:text-3xl font-bold mb-2 sm:mb-3 text-gray-800">Instrument Lessons</h2>
        <p class="text-center text-gray-800 mb-6 sm:mb-8 md:mb-10 text-sm sm:text-base px-2">Choose from our comprehensive range of instrument courses designed for all skill levels</p>
        
        <div class="grid grid-cols-3 gap-2 sm:gap-3 md:gap-4 max-w-6xl mx-auto">
            <div class="bg-white rounded-lg sm:rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103331/Aluminum_Snare_Drum_osfn9z.jpg" class="w-full h-24 sm:h-32 md:h-36 lg:h-44 object-cover">
                <div class="p-2 sm:p-3 md:p-4 lg:p-5">
                    <h3 class="text-xs sm:text-sm md:text-base lg:text-lg font-semibold mb-1 text-gray-800">Snare Drum</h3>
                    <p class="text-gray-600 mb-2 sm:mb-4 md:mb-7 text-[10px] sm:text-xs md:text-sm leading-tight">Master rudiments, grooves, and dynamic control on high-quality aluminum snare drums</p>
                </div>
            </div>

            <div class="bg-white rounded-lg sm:rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103331/American_Red_Gum_ujcveg.jpg" alt="American Red Gum" class="w-full h-24 sm:h-32 md:h-36 lg:h-44 object-cover">
                <div class="p-2 sm:p-3 md:p-4 lg:p-5">
                    <h3 class="text-xs sm:text-sm md:text-base lg:text-lg font-semibold mb-1 text-gray-800">Drum Kit</h3>
                    <p class="text-gray-600 mb-2 sm:mb-4 md:mb-7 text-[10px] sm:text-xs md:text-sm leading-tight">Learn fingerstyle, strumming, and chords on beautiful American red gum acoustics</p>
                </div>
            </div>

            <div class="bg-white rounded-lg sm:rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103332/Beatbox_glezrv.jpg" alt="Beatbox" class="w-full h-24 sm:h-32 md:h-36 lg:h-44 object-cover">
                <div class="p-2 sm:p-3 md:p-4 lg:p-5">
                    <h3 class="text-xs sm:text-sm md:text-base lg:text-lg font-semibold mb-1 text-gray-800">Beatbox & Cajon</h3>
                    <p class="text-gray-600 mb-2 sm:mb-4 md:mb-7 text-[10px] sm:text-xs md:text-sm leading-tight">Develop rhythm, bass tones, and percussion skills using cajon and beatbox techniques</p>
                </div>
            </div>

            <div class="bg-white rounded-lg sm:rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103334/Chameleon_pq0uaw.jpg" alt="Chameleon" class="w-full h-24 sm:h-32 md:h-36 lg:h-44 object-cover">
                <div class="p-2 sm:p-3 md:p-4 lg:p-5">
                    <h3 class="text-xs sm:text-sm md:text-base lg:text-lg font-semibold mb-1 text-gray-800">Acoustic Guitar</h3>
                    <p class="text-gray-600 mb-2 sm:mb-4 md:mb-7 text-[10px] sm:text-xs md:text-sm leading-tight">Explore leads, rhythms, and effects on versatile Chameleon finish electrics</p>
                </div>
            </div>

            <div class="bg-white rounded-lg sm:rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103325/Dark_Teal_fbe4bl.jpg" alt="Dark Teal" class="w-full h-24 sm:h-32 md:h-36 lg:h-44 object-cover">
                <div class="p-2 sm:p-3 md:p-4 lg:p-5">
                    <h3 class="text-xs sm:text-sm md:text-base lg:text-lg font-semibold mb-1 text-gray-800">Drum Kit</h3>
                    <p class="text-gray-600 mb-2 sm:mb-4 md:mb-7 text-[10px] sm:text-xs md:text-sm leading-tight">Build power, timing, and fills on striking Dark Teal drum kits</p>
                </div>
            </div>

            <div class="bg-white rounded-lg sm:rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103326/Division_Black_Maple_jnpzwq.jpg" alt="Division Black Maple" class="w-full h-24 sm:h-32 md:h-36 lg:h-44 object-cover">
                <div class="p-2 sm:p-3 md:p-4 lg:p-5">
                    <h3 class="text-xs sm:text-sm md:text-base lg:text-lg font-semibold mb-1 text-gray-800">Rock Drums</h3>
                    <p class="text-gray-600 mb-2 sm:mb-4 md:mb-7 text-[10px] sm:text-xs md:text-sm leading-tight">Focus on heavy grooves and dynamics with Division Black Maple shells</p>
                </div>
            </div>

            <div class="bg-white rounded-lg sm:rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103326/Drums_dkcswt.jpg" alt="Drums" class="w-full h-24 sm:h-32 md:h-36 lg:h-44 object-cover">
                <div class="p-2 sm:p-3 md:p-4 lg:p-5">
                    <h3 class="text-xs sm:text-sm md:text-base lg:text-lg font-semibold mb-1 text-gray-800">Complete Drums</h3>
                    <p class="text-gray-600 mb-2 sm:mb-4 md:mb-7 text-[10px] sm:text-xs md:text-sm leading-tight">Full drum set training – from basics to advanced independence</p>
                </div>
            </div>

            <div class="bg-white rounded-lg sm:rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103326/Piano_fc2cp7.jpg" alt="Piano" class="w-full h-24 sm:h-32 md:h-36 lg:h-44 object-cover">
                <div class="p-2 sm:p-3 md:p-4 lg:p-5">
                    <h3 class="text-xs sm:text-sm md:text-base lg:text-lg font-semibold mb-1 text-gray-800">Piano & Keyboard</h3>
                    <p class="text-gray-600 mb-2 sm:mb-4 md:mb-7 text-[10px] sm:text-xs md:text-sm leading-tight">Learn scales, chords, and songs on digital and acoustic pianos</p>
                </div>
            </div>

            <div class="bg-white rounded-lg sm:rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103328/Raven_xqlvmz.jpg" alt="Raven" class="w-full h-24 sm:h-32 md:h-36 lg:h-44 object-cover">
                <div class="p-2 sm:p-3 md:p-4 lg:p-5">
                    <h3 class="text-xs sm:text-sm md:text-base lg:text-lg font-semibold mb-1 text-gray-800">Modern Guitar</h3>
                    <p class="text-gray-600 mb-2 sm:mb-4 md:mb-7 text-[10px] sm:text-xs md:text-sm leading-tight">Contemporary styles and techniques on Raven series electrics</p>
                </div>
            </div>

            <div class="bg-white rounded-lg sm:rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103329/Resurrector_wuzr04.jpg" alt="Resurrector" class="w-full h-24 sm:h-32 md:h-36 lg:h-44 object-cover">
                <div class="p-2 sm:p-3 md:p-4 lg:p-5">
                    <h3 class="text-xs sm:text-sm md:text-base lg:text-lg font-semibold mb-1 text-gray-800">Bass Guitar</h3>
                    <p class="text-gray-600 mb-2 sm:mb-4 md:mb-7 text-[10px] sm:text-xs md:text-sm leading-tight">Groove, slap, and fingerstyle on powerful Resurrector basses</p>
                </div>
            </div>

            <div class="bg-white rounded-lg sm:rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769105598/voice_g2pqu1.png" alt="Voice" class="w-full h-24 sm:h-32 md:h-36 lg:h-44 object-cover">
                <div class="p-2 sm:p-3 md:p-4 lg:p-5">
                    <h3 class="text-xs sm:text-sm md:text-base lg:text-lg font-semibold mb-1 text-gray-800">Voice Lessons</h3>
                    <p class="text-gray-600 mb-2 sm:mb-4 md:mb-7 text-[10px] sm:text-xs md:text-sm leading-tight">Comprehensive voice training, from vocal fundamentals to advanced techniques</p>
                </div>
            </div>

            <div class="bg-white rounded-lg sm:rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769105258/violin_d6nnx8.jpg" alt="Violin" class="w-full h-24 sm:h-32 md:h-36 lg:h-44 object-cover">
                <div class="p-2 sm:p-3 md:p-4 lg:p-5">
                    <h3 class="text-xs sm:text-sm md:text-base lg:text-lg font-semibold mb-1 text-gray-800">Violin</h3>
                    <p class="text-gray-600 mb-2 sm:mb-4 md:mb-7 text-[10px] sm:text-xs md:text-sm leading-tight">Violin lessons covering essential basics up to advanced mastery</p>
                </div>
            </div>

        </div>
    </section>

    <section class="py-16 px-[5%] bg-gray-100">
        <h2 class="text-center text-3xl font-bold mb-3 text-gray-800">Our Products</h2>
        <p class="text-center text-gray-600 mb-2 text-base">Premium instruments and accessories for musicians at every level</p>
        <p class="text-center text-gray-600 mb-8 text-sm">Visit our store to purchase these quality instruments</p>

        <div class="grid grid-flow-col grid-rows-4 gap-4 max-w-6xl mx-auto overflow-hidden">

        <div class="group bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="overflow-hidden">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103331/Aluminum_Snare_Drum_osfn9z.jpg" alt="Aluminum Snare Drum"
                    class="w-full h-28 object-cover border-b border-gray-200 transition-transform duration-300 group-hover:scale-105">
            </div>
            <div class="p-3">
                <span class="inline-block bg-[#d4af7a] text-gray-800 px-2 py-0.5 rounded-full text-[10px] mb-1">Snare</span>
                <h3 class="text-sm font-semibold mb-0.5 text-gray-800">Aluminum Snare Drum</h3>
                <div class="text-base font-bold text-gray-800">₱9,999</div>
            </div>
        </div>

        <div class="group bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="overflow-hidden">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103331/American_Red_Gum_ujcveg.jpg" alt="American Red Gum"
                    class="w-full h-28 object-cover border-b border-gray-200 transition-transform duration-300 group-hover:scale-105">
            </div>
            <div class="p-3">
                <span class="inline-block bg-[#d4af7a] text-gray-800 px-2 py-0.5 rounded-full text-[10px] mb-1">Drum Kit</span>
                <h3 class="text-sm font-semibold mb-0.5 text-gray-800">American Red Gum</h3>
                <div class="text-base font-bold text-gray-800">₱10,500</div>
            </div>
        </div>

        <div class="group bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="overflow-hidden">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103332/Beatbox_glezrv.jpg" alt="Beatbox"
                    class="w-full h-28 object-cover border-b border-gray-200 transition-transform duration-300 group-hover:scale-105">
            </div>
            <div class="p-3">
                <span class="inline-block bg-[#d4af7a] text-gray-800 px-2 py-0.5 rounded-full text-[10px] mb-1">Cajon</span>
                <h3 class="text-sm font-semibold mb-0.5 text-gray-800">Beatbox Cajon</h3>
                <div class="text-base font-bold text-gray-800">₱6,800</div>
            </div>
        </div>

        <div class="group bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="overflow-hidden">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103334/Chameleon_pq0uaw.jpg" alt="Chameleon"
                    class="w-full h-28 object-cover border-b border-gray-200 transition-transform duration-300 group-hover:scale-105">
            </div>
            <div class="p-3">
                <span class="inline-block bg-[#d4af7a] text-gray-800 px-2 py-0.5 rounded-full text-[10px] mb-1">Acoustic Guitar</span>
                <h3 class="text-sm font-semibold mb-0.5 text-gray-800">Chameleon</h3>
                <div class="text-base font-bold text-gray-800">₱3,900</div>
            </div>
        </div>

        <div class="group bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="overflow-hidden">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103325/Dark_Teal_fbe4bl.jpg" alt="Dark Teal"
                    class="w-full h-28 object-cover border-b border-gray-200 transition-transform duration-300 group-hover:scale-105">
            </div>
            <div class="p-3">
                <span class="inline-block bg-[#d4af7a] text-gray-800 px-2 py-0.5 rounded-full text-[10px] mb-1">Drum Kit</span>
                <h3 class="text-sm font-semibold mb-0.5 text-gray-800">Dark Teal Maple</h3>
                <div class="text-base font-bold text-gray-800">₱6,000</div>
            </div>
        </div>

        <div class="group bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="overflow-hidden">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103326/Division_Black_Maple_jnpzwq.jpg" alt="Division Black Maple"
                    class="w-full h-28 object-cover border-b border-gray-200 transition-transform duration-300 group-hover:scale-105">
            </div>
            <div class="p-3">
                <span class="inline-block bg-[#d4af7a] text-gray-800 px-2 py-0.5 rounded-full text-[10px] mb-1">Drum Kit</span>
                <h3 class="text-sm font-semibold mb-0.5 text-gray-800">Division Black Maple</h3>
                <div class="text-base font-bold text-gray-800">₱7,500</div>
            </div>
        </div>

        <div class="group bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="overflow-hidden">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103326/Drums_dkcswt.jpg" alt="Drums"
                    class="w-full h-28 object-cover border-b border-gray-200 transition-transform duration-300 group-hover:scale-105">
            </div>
            <div class="p-3">
                <span class="inline-block bg-[#d4af7a] text-gray-800 px-2 py-0.5 rounded-full text-[10px] mb-1">Full Kit</span>
                <h3 class="text-sm font-semibold mb-0.5 text-gray-800">Professional Drum Set</h3>
                <div class="text-base font-bold text-gray-800">₱95,000</div>
            </div>
        </div>

        <div class="group bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="overflow-hidden">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103326/Piano_fc2cp7.jpg" alt="Piano"
                    class="w-full h-28 object-cover border-b border-gray-200 transition-transform duration-300 group-hover:scale-105">
            </div>
            <div class="p-3">
                <span class="inline-block bg-[#d4af7a] text-gray-800 px-2 py-0.5 rounded-full text-[10px] mb-1">Digital Piano</span>
                <h3 class="text-sm font-semibold mb-0.5 text-gray-800">Digital Piano 88-Key</h3>
                <div class="text-base font-bold text-gray-800">₱8,900</div>
            </div>
        </div>

        <div class="group bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="overflow-hidden">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103328/Raven_xqlvmz.jpg" alt="Raven"
                    class="w-full h-28 object-cover border-b border-gray-200 transition-transform duration-300 group-hover:scale-105">
            </div>
            <div class="p-3">
                <span class="inline-block bg-[#d4af7a] text-gray-800 px-2 py-0.5 rounded-full text-[10px] mb-1">Electric Guitar</span>
                <h3 class="text-sm font-semibold mb-0.5 text-gray-800">Raven Series</h3>
                <div class="text-base font-bold text-gray-800">₱10,500</div>
            </div>
        </div>

        <div class="group bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="overflow-hidden">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103329/Resurrector_wuzr04.jpg" alt="Resurrector"
                    class="w-full h-28 object-cover border-b border-gray-200 transition-transform duration-300 group-hover:scale-105">
            </div>
            <div class="p-3">
                <span class="inline-block bg-[#d4af7a] text-gray-800 px-2 py-0.5 rounded-full text-[10px] mb-1">Electric Guitar</span>
                <h3 class="text-sm font-semibold mb-0.5 text-gray-800">Resurrector</h3>
                <div class="text-base font-bold text-gray-800">₱10,900</div>
            </div>
        </div>

        <div class="group bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="overflow-hidden">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103330/Sub_Zero_White_Maple_ji0vm1.jpg" alt="Sub Zero White Maple"
                    class="w-full h-28 object-cover border-b border-gray-200 transition-transform duration-300 group-hover:scale-105">
            </div>
            <div class="p-3">
                <span class="inline-block bg-[#d4af7a] text-gray-800 px-2 py-0.5 rounded-full text-[10px] mb-1">Drum Kit</span>
                <h3 class="text-sm font-semibold mb-0.5 text-gray-800">Sub Zero White Maple</h3>
                <div class="text-base font-bold text-gray-800">₱9,900</div>
            </div>
        </div>

    </div>

        
        <button class="block mx-auto mt-10 px-8 py-3 bg-gray-800 text-white text-sm rounded-md cursor-pointer hover:bg-gray-900" onclick="alert('Visit our store at Mango Square Mall, Juana Osmeña, Brgy. Kamputhaw 6000 Cebu City, Philippines to see all products!')">View All Products</button>
    </section>

    <section class="py-12 px-[5%] bg-gradient-to-br from-gray-50 to-gray-100">
        <h2 class="text-center text-2xl font-bold mb-2 text-[#272829]">Leave your review</h2>
        <p class="text-center text-[#61677A] mb-6 text-sm">Share your experience with Music Lab</p>
        
        <div class="max-w-lg mx-auto bg-white p-5 rounded-lg shadow-md border border-gray-200">
            <form id="reviewForm">
                @csrf
                <div class="mb-4">
                    <label class="block mb-1.5 text-[#272829] font-medium text-xs">Your rating</label>
                    <div class="flex gap-1.5 text-2xl" id="starRating">
                        <span class="star cursor-pointer text-gray-300 hover:text-[#C2922F] transition-colors" data-rating="1">★</span>
                        <span class="star cursor-pointer text-gray-300 hover:text-[#C2922F] transition-colors" data-rating="2">★</span>
                        <span class="star cursor-pointer text-gray-300 hover:text-[#C2922F] transition-colors" data-rating="3">★</span>
                        <span class="star cursor-pointer text-gray-300 hover:text-[#C2922F] transition-colors" data-rating="4">★</span>
                        <span class="star cursor-pointer text-gray-300 hover:text-[#C2922F] transition-colors" data-rating="5">★</span>
                    </div>
                    <input type="hidden" id="ratingValue" name="rating" value="0">
                    <span id="ratingError" class="text-red-500 text-xs hidden">Please select a rating</span>
                </div>
                
                <div class="mb-4">
                    <label for="reviewerName" class="block mb-1.5 text-[#272829] font-medium text-xs">Your name</label>
                    <input type="text" id="reviewerName" name="reviewer_name" required placeholder="Enter your name" class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#377357] focus:border-transparent">
                </div>
                
                <div class="mb-4">
                    <label for="reviewText" class="block mb-1.5 text-[#272829] font-medium text-xs">Your review</label>
                    <textarea id="reviewText" name="review_text" required placeholder="Share your experience..." class="w-full px-3 py-2 border border-gray-300 rounded text-sm resize-y min-h-[80px] max-h-[200px] focus:outline-none focus:ring-2 focus:ring-[#377357] focus:border-transparent"></textarea>
                </div>
                
                <button type="submit" id="submitBtn" class="w-full py-2.5 bg-[#377357] text-white text-sm rounded font-medium hover:bg-[#2d5f48] transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Submit review
                </button>
            </form>
        </div>

        <div class="max-w-4xl mx-auto mt-8" id="reviewsList">
            <h3 class="text-center mb-5 text-xl font-semibold text-[#272829]">Recent reviews</h3>
            <div id="reviewsContainer">
                <!-- Reviews loaded via JavaScript -->
            </div>
            
            <!-- Pagination -->
            <div id="paginationContainer" class="mt-6 flex justify-center items-center gap-2">
                <!-- Pagination loaded via JavaScript -->
            </div>
            
            <!-- Total Reviews Count -->
            <div class="text-center mt-4">
                <p class="text-sm text-[#61677A]">Total reviews: <span id="totalReviews" class="font-semibold text-[#272829]">0</span></p>
            </div>
        </div>
    </section>

   <section class="py-8 sm:py-12 md:py-16 px-[5%] bg-gray-800 text-center text-white">
        <h2 class="text-xl sm:text-2xl md:text-3xl font-bold mb-2 sm:mb-3 px-4">Ready to Start Your Musical Journey?</h2>
        <p class="text-xs sm:text-sm md:text-base mb-6 sm:mb-8 max-w-2xl mx-auto px-4">Join thousands of students learning music online with Music Lab. Get access to expert instruction and quality instruments today.</p>
        <a href="/register" class="inline-block bg-[#d4af7a] text-gray-800 px-4 sm:px-6 py-2 rounded-md text-xs sm:text-sm font-medium transition-colors hover:bg-[#c49d68]">Get Started Now</a>
    </section>

    <footer class="bg-[#3d4f5d] text-white py-6 sm:py-8 md:py-12 px-[3%] sm:px-[5%]">
        <div class="grid grid-cols-4 gap-2 sm:gap-4 md:gap-6 mb-4 sm:mb-6 md:mb-8">
            <div>
                <a href="{{ route('home') }}">
                    <img 
                        src="https://res.cloudinary.com/dibojpqg2/image/upload/v1769103286/logo_crllhy.png" 
                        alt="Music Lab Logo" 
                        class="h-16 sm:h-24 md:h-32 lg:h-40 w-auto"
                    />
                </a>
            </div>

            <div>
                <h3 class="mb-1 sm:mb-2 md:mb-3 text-[10px] sm:text-sm md:text-base lg:text-lg font-semibold">Courses</h3>
                <ul class="list-none space-y-0.5 sm:space-y-1 md:space-y-2">
                    <li><a href="#" class="text-[9px] sm:text-xs md:text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Piano Lessons</a></li>
                    <li><a href="#" class="text-[9px] sm:text-xs md:text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Guitar Lessons</a></li>
                    <li><a href="#" class="text-[9px] sm:text-xs md:text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Violin Lessons</a></li>
                    <li><a href="#" class="text-[9px] sm:text-xs md:text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Drum Lessons</a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="mb-1 sm:mb-2 md:mb-3 text-[10px] sm:text-sm md:text-base lg:text-lg font-semibold">Shop</h3>
                <ul class="list-none space-y-0.5 sm:space-y-1 md:space-y-2">
                    <li><a href="#" class="text-[9px] sm:text-xs md:text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Instruments</a></li>
                    <li><a href="#" class="text-[9px] sm:text-xs md:text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Accessories</a></li>
                    <li><a href="#" class="text-[9px] sm:text-xs md:text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Sheet Music</a></li>
                    <li><a href="#" class="text-[9px] sm:text-xs md:text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Gift Cards</a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="mb-1 sm:mb-2 md:mb-3 text-[10px] sm:text-sm md:text-base lg:text-lg font-semibold">Contact</h3>
                <ul class="list-none space-y-0.5 sm:space-y-1 md:space-y-2 text-[9px] sm:text-xs md:text-sm text-gray-300">

                    <li class="flex items-start gap-1">
                        <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 md:w-4 md:h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                        <a href="mailto:musiclabcebu@gmail.com" class="hover:underline break-all leading-tight">musiclabcebu@gmail.com</a>
                    </li>
                    
                    <li class="flex items-start gap-1">
                        <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 md:w-4 md:h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                        </svg>
                        <a href="tel:09331730733" class="hover:underline leading-tight">0933 173 0733</a>
                    </li>
                    
                    <li class="flex items-start gap-1">
                        <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 md:w-4 md:h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <a href="https://www.google.com/maps/place/Music+Lab/@10.3109795,123.8928062,17z/data=!3m1!4b1!4m6!3m5!1s0x33a9994cf36639ad:0xc50878433d6a361d!8m2!3d10.3109742!4d123.8953811!16s%2Fg%2F11gj6c8m1l?entry=ttu&g_ep=EgoyMDI2MDEyMC4wIKXMDSoKLDEwMDc5MjA2OUgBUAM%3D" 
                        target="_blank" class="hover:underline leading-tight">
                        Mango Square Mall, Juana Osmeña, Brgy. Kamputhaw 6000 Cebu City, Philippines
                        </a>
                    </li>

                </ul>
            </div>

        </div>
        
        <div class="text-center pt-3 sm:pt-4 md:pt-6 lg:pt-8 border-t border-gray-600 text-[9px] sm:text-xs md:text-sm text-gray-400">
            <p>© 2026 Music Lab. All rights reserved.</p>
        </div>
    </footer>

    <script>
        let currentPage = 1;
        const stars = document.querySelectorAll('.star');
        const ratingValue = document.getElementById('ratingValue');
        const ratingError = document.getElementById('ratingError');
        let selectedRating = 0;

        // Star rating functionality
        stars.forEach(star => {
            star.addEventListener('click', function() {
                selectedRating = this.getAttribute('data-rating');
                ratingValue.value = selectedRating;
                ratingError.classList.add('hidden');
                updateStars(selectedRating);
            });

            star.addEventListener('mouseover', function() {
                const hoverRating = this.getAttribute('data-rating');
                updateStars(hoverRating);
            });
        });

        document.getElementById('starRating').addEventListener('mouseout', function() {
            updateStars(selectedRating);
        });

        function updateStars(rating) {
            stars.forEach(star => {
                const starRating = star.getAttribute('data-rating');
                if (starRating <= rating) {
                    star.classList.remove('text-gray-300');
                    star.classList.add('text-[#C2922F]');
                } else {
                    star.classList.remove('text-[#C2922F]');
                    star.classList.add('text-gray-300');
                }
            });
        }

        // Load reviews
        function loadReviews(page = 1) {
            fetch(`/reviews?page=${page}`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('reviewsContainer');
                    const paginationContainer = document.getElementById('paginationContainer');
                    const totalReviewsSpan = document.getElementById('totalReviews');
                    
                    // Update total count
                    totalReviewsSpan.textContent = data.total;
                    
                    // Render reviews
                    if (data.data.length === 0) {
                        container.innerHTML = '<p class="text-center text-[#61677A] text-sm py-8">No reviews yet. Be the first to share your experience!</p>';
                        paginationContainer.innerHTML = '';
                        return;
                    }
                    
                    container.innerHTML = data.data.map(review => `
                        <div class="bg-gradient-to-r from-[#FFF6E0] to-white p-4 rounded-lg mb-3 border-l-4 border-[#C2922F] shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-[#377357] text-white rounded-full flex items-center justify-center text-xs font-bold">
                                        ${review.reviewer_name.charAt(0).toUpperCase()}
                                    </div>
                                    <span class="font-semibold text-[#272829] text-sm">${escapeHtml(review.reviewer_name)}</span>
                                </div>
                                <div class="flex gap-0.5">
                                    ${'★'.repeat(review.rating)}<span class="text-gray-300">${'★'.repeat(5 - review.rating)}</span>
                                </div>
                            </div>
                            <p class="text-[#61677A] text-sm leading-relaxed">${escapeHtml(review.review_text)}</p>
                            <p class="text-xs text-gray-400 mt-2">${formatDate(review.created_at)}</p>
                        </div>
                    `).join('');
                    
                    // Render pagination
                    renderPagination(data, paginationContainer);
                })
                .catch(error => {
                    console.error('Error loading reviews:', error);
                    document.getElementById('reviewsContainer').innerHTML = '<p class="text-center text-red-500 text-sm">Failed to load reviews</p>';
                });
        }

        function renderPagination(data, container) {
            if (data.last_page <= 1) {
                container.innerHTML = '';
                return;
            }
            
            let html = '';
            
            // Previous button
            if (data.current_page > 1) {
                html += `<button onclick="loadReviews(${data.current_page - 1})" class="px-3 py-1.5 bg-[#377357] text-white text-xs rounded hover:bg-[#2d5f48] transition-colors">Previous</button>`;
            }
            
            // Page numbers
            for (let i = 1; i <= data.last_page; i++) {
                if (i === data.current_page) {
                    html += `<span class="px-3 py-1.5 bg-[#C2922F] text-white text-xs rounded font-semibold">${i}</span>`;
                } else {
                    html += `<button onclick="loadReviews(${i})" class="px-3 py-1.5 bg-gray-200 text-[#272829] text-xs rounded hover:bg-gray-300 transition-colors">${i}</button>`;
                }
            }
            
            // Next button
            if (data.current_page < data.last_page) {
                html += `<button onclick="loadReviews(${data.current_page + 1})" class="px-3 py-1.5 bg-[#377357] text-white text-xs rounded hover:bg-[#2d5f48] transition-colors">Next</button>`;
            }
            
            container.innerHTML = html;
        }

        // Submit review
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const rating = ratingValue.value;
            const name = document.getElementById('reviewerName').value.trim();
            const text = document.getElementById('reviewText').value.trim();
            const submitBtn = document.getElementById('submitBtn');
            
            if (rating == 0) {
                ratingError.classList.remove('hidden');
                return;
            }
            
            if (!name || !text) return;
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
            
            fetch('/reviews', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    reviewer_name: name,
                    rating: parseInt(rating),
                    review_text: text
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.reset();
                    selectedRating = 0;
                    updateStars(0);
                    loadReviews(1);
                    alert('Thank you for your review!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to submit review. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit review';
            });
        });

        // Helper functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        // Load reviews on page load
        loadReviews(1);
    </script>

</body>
</html>