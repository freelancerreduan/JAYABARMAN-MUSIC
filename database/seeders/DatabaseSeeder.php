<?php

namespace Database\Seeders;

use App\Models\AboutBlock;
use App\Models\Album;
use App\Models\GalleryImage;
use App\Models\MusicVideo;
use App\Models\PortfolioGroup;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Starts the site with the same content as the static HTML version. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Two logins: one for the developer (testing), one for the client.
        // Starting password = the email; each user can change email and password in "Footer links & login".
        foreach ([['Developer', 'developer@gmail.com'], ['Jaya Barman', 'admin@gmail.com']] as [$name, $email]) {
            if (! User::where('email', $email)->exists()) {
                User::create(['name' => $name, 'email' => $email, 'password' => $email]);
            }
        }

        // On first install the setup page can choose the client's admin email and password.
        $adminEmail = getenv('ADMIN_EMAIL') ?: ($_SERVER['ADMIN_EMAIL'] ?? '');
        $adminPassword = getenv('ADMIN_PASSWORD') ?: ($_SERVER['ADMIN_PASSWORD'] ?? '');
        if ($adminEmail && $adminPassword && ! User::where('email', $adminEmail)->exists()) {
            User::create(['name' => 'Admin', 'email' => $adminEmail, 'password' => $adminPassword]);
        }

        // Only fills settings that are not saved yet, so running the seeder again keeps the admin's changes
        Setting::putMissing([
            'hero_image' => 'assets/img/hero.jpg',
            'hero_name' => 'Jaya Barman',
            'hero_tagline' => '…spirit <b>U</b> all…',
            'home_title' => 'Musician',
            'home_text' => '<p>Jaya Barman is a distinguished visual artist, actor, and professional musician, with a Master of Fine Arts degree from the University of Dhaka. Jaya currently serves as the lead member of <a href="http://www.jolergaan.com" target="_blank" rel="noopener">Joler Gaan</a>, a musical group based in Dhaka, Bangladesh, while also engaging in television, film, and stage productions. Jaya is also a lead actor and musician with <a href="http://www.prachyanat.com" target="_blank" rel="noopener">Prachyanat</a>, a prominent theater group recognized for its impactful social commentary. Jaya identifies as a child of the soil, drawing inspiration from the rivers that cleanse and carry the gentle, soothing symphonies of nature. On contemplative nights, dreams navigate like a boat across these fertile lands, fueled by a profound curiosity regarding the melodies of the natural world. Jaya is particularly passionate about composing songs, designing innovative musical instruments, and delving into digital film and multimedia art. Jaya has participated in various residency programs across the UK, Germany, Sri Lanka, Japan, India, South Korea, Nepal, Vietnam, Egypt, and Brazil. Over the years, Jaya has collaborated on numerous projects with esteemed artists and organizations, including Colin Blakey from the UK, Farzo Abaji from France, Amy Trumppetar from the USA, Sacatar Foundation from Brazil, Jerome Philip Kavanagh and Komako-Aroha Silva from New Zealand, and Max Vandervorst from Belgium. Jaya firmly believes in the transformative power of art and its capacity to forge connections among diverse cultures and experiences. Through this work, Jaya aspires to inspire others to discover their own voices and articulate their unique narratives. Everyone is invited to join this artistic journey to explore the beauty of creativity together.</p>',
            'about_photo' => 'assets/img/portrait.jpg',
            'about_details_title' => 'Personal Details',
            'about_name' => 'JAYA BARMAN',
            'about_role' => 'Actor, Musician',
            'about_email' => 'tukuli17@gmail.com',
            'contact_image' => 'assets/img/jaya-live.jpg',
            'contact_title' => 'Get in Touch',
            'contact_location' => 'Paris, France',
            'contact_whatsapp' => '8801711046714',
            'contact_email' => 'tukuli17@gmail.com',
            'social_facebook' => 'https://www.facebook.com/',
            'social_instagram' => 'https://www.instagram.com/',
            'social_spotify' => 'https://open.spotify.com/',
            'social_youtube' => 'https://www.youtube.com/',
            'social_whatsapp' => 'https://wa.me/8801711046714',
        ]);

        // Content tables are filled only once, so running the seeder again never duplicates or overwrites admin work
        if (Album::exists() || GalleryImage::exists() || PortfolioGroup::exists() || AboutBlock::exists() || MusicVideo::exists()) {
            return;
        }

        $v = 'assets/video/';
        $albums = [
            'লোকগান · Bengali Folk' => [
                ['আমি তোর পিরিতের মরা · Ami Tor Piriter Mora', $v.'performance-01.mp4'],
                ['প্রেম কলঙ্কের জ্বালা কত রে · Prem Kolonker Jwala', $v.'performance-02.mp4'],
                ['রাঙা সকাল · Bengali Folk on Ranga Shokal', $v.'performance-03.mp4'],
            ],
            'ভালো আছি ভালো থেকো · Bengali Romantic' => [
                ['ভালো আছি, ভালো থেকো · Bhalo Achhi, Bhalo Theko', $v.'performance-04.mp4'],
                ['আমি তোর পিরিতের মরা · Ami Tor Piriter Mora', $v.'performance-01.mp4'],
            ],
            'World Music Day 2026 · Live in Paris' => [
                ['World Music Day · Live in Aubervilliers', $v.'project-01.mp4'],
                ['World Music Day · Shobdo Taranga, Paris', $v.'project-02.mp4'],
                ['World Music Day · Shobdo Taranga (part 2)', $v.'project-03.mp4'],
            ],
        ];
        $i = 0;
        foreach ($albums as $title => $tracks) {
            $album = Album::create(['title' => $title, 'sort' => ++$i]);
            foreach ($tracks as $n => [$t, $file]) {
                $album->tracks()->create(['title' => $t, 'file' => $file, 'sort' => $n + 1]);
            }
        }

        $videos = [
            ['আমি তোর পিরিতের মরা', 'Jaya Barman', 'file', $v.'performance-01.mp4'],
            ['প্রেম কলঙ্কের জ্বালা', 'Jaya Barman', 'file', $v.'performance-02.mp4'],
            ['রাঙা সকাল · Maasranga TV', 'Jaya Barman', 'file', $v.'performance-03.mp4'],
            ['ভালো আছি, ভালো থেকো', 'Jaya Barman', 'file', $v.'performance-04.mp4'],
            ['World Music Day 2026', 'Live in Aubervilliers', 'file', $v.'project-01.mp4'],
            ['দিন প্রতিদিন · Din Protidin', 'Bangla Vision', 'youtube', 'HSO6v9n220w'],
            ['World Music Day 2026', 'Shobdo Taranga, Paris', 'file', $v.'project-02.mp4'],
            ['World Music Day 2026', 'Shobdo Taranga, Paris', 'file', $v.'project-03.mp4'],
        ];
        foreach ($videos as $n => [$title, $sub, $type, $src]) {
            MusicVideo::create(['title' => $title, 'subtitle' => $sub, 'type' => $type, 'source' => $src, 'sort' => $n + 1]);
        }

        $img = fn (array $names) => array_map(fn ($n) => 'assets/img/'.$n, $names);
        $stage = fn (...$n) => array_map(fn ($x) => sprintf('stage-%02d.jpg', $x), $n);

        $galleries = [
            'visual-art' => $img(array_merge(['portrait.jpg', 'jaya-garden.webp', 'world-music-day.jpg', 'jaya-duo.jpg'], $stage(1, 2, 3, 4, 5, 6, 7, 8))),
            'theatre' => $img(array_merge(['jaya-live.jpg'], $stage(2, 5, 6, 3, 8))),
        ];
        foreach ($galleries as $section => $files) {
            foreach ($files as $n => $file) {
                GalleryImage::create(['section' => $section, 'image' => $file, 'sort' => $n + 1]);
            }
        }

        $groups = [
            'Instruments' => array_merge($stage(1, 2, 3, 4, 5, 6, 7, 8), ['portrait.jpg', 'jaya-garden.webp']),
            'Collaboration' => array_merge(['jaya-duo.jpg', 'world-music-day.jpg', 'jaya-live.jpg'], $stage(4, 5, 6, 7, 8, 1, 2)),
            'My Musical Team · Joler Gaan' => array_merge($stage(3), ['jaya-duo.jpg'], $stage(5, 6, 7, 1)),
            'Musical Procession · Laal Jatra' => array_merge($stage(2, 4, 6, 8), ['world-music-day.jpg'], $stage(1)),
            'Performance Art' => array_merge(['jaya-live.jpg'], $stage(7, 3, 5, 8, 2)),
            'On Stage' => array_merge($stage(5, 6, 1), ['portrait.jpg'], $stage(2)),
            'My Body' => array_merge(['portrait.jpg', 'jaya-garden.webp'], $stage(4, 8)),
            'Site Specific Artwork' => array_merge(['jaya-garden.webp'], $stage(3, 7, 1)),
        ];
        $g = 0;
        foreach ($groups as $title => $files) {
            $group = PortfolioGroup::create(['title' => $title, 'sort' => ++$g]);
            foreach ($img($files) as $n => $file) {
                $group->images()->create(['image' => $file, 'sort' => $n + 1]);
            }
        }

        $ul = fn (array $items) => '<ul>'.implode('', array_map(fn ($x) => '<li>'.$x.'</li>', $items)).'</ul>';
        $blocks = [
            ['right', 'Current Status', '<p><b>Founder Member</b><br>Prachyanat (Theatre Group)<br><a href="http://www.prachyanat.com" target="_blank" rel="noopener">www.prachyanat.com</a></p><p><b>Team Leader</b><br>Joler Gaan (Musical troupe)<br><a href="http://www.jolergaan.com" target="_blank" rel="noopener">www.jolergaan.com</a></p><p><b>Trustee</b><br>Crack International Art camp<br><a href="http://www.crackbd.org" target="_blank" rel="noopener">www.crackbd.org</a></p><p><b>Associate</b><br>Edinburgh Mela Ltd.<br>Melange Network UK<br>Banglascot Foundation<br>PANDAL (Festival Organizer)</p>'],
            ['left', 'Work Experience', '<p>2008-2010: Creative Director, Dhansiri communication ltd, Bangladesh</p>'],
            ['left', 'Residency Program', $ul([
                'Artist in Residency Program (Major)',
                '2003: Eight weeks long residency in Edinburgh festival, Scotland. U.K',
                '2004: Two weeks long Residency in Tokyo Organized by Japan Foundation, Japan.',
                '2004: Eight weeks long residency in Edinburgh festival, Scotland. U.K.',
                '2005: Two weeks long Residency in WIPSA Theatre Festival, India.',
                '2006: Two weeks long residency in “World Sufi Festival” Glasgow Scotland. U.K.',
                '2006: Performed in different concerts in the U.K with “Joler Gaan” (Musical Band)',
                '2008: Eight weeks long residency in Brazil organized by Sacatar Foundation, Bahia, Brazil',
                '2013: Four weeks long residency in Soundcamp South Asia, Colombo, Srilanka',
                '2014: A week long residency in Worldtronics, Berlin, Germany',
                '2014: Six Months long residency in NTOK-CPI-14, Seoul, South Korea',
                '2010-Present: CRACK International Art Camp, Kustia, Bangladesh.',
            ])],
            ['left', 'Countries visited with Art projects', '<p>Scotland, England, Brazil, Japan, Egypt, India, Nepal, Sri Lanka, Germany, UAE, Vietnam, South Korea, Australia</p>'],
            ['left', 'Dedications and Devotions', $ul([
                'Musical instruments collection',
                'Making musical instruments with local elements',
                'Research on traditional music and musical instruments',
                'Writing, composing, and producing music',
            ])],
            ['left', 'Artistic Collaborations', $ul([
                'Colin Blakey (Scotland)', 'Kim Ho Ip (China/UK)', 'Farzo Abaji (France)', 'Amy Trumppeter (USA)',
                'Sacatar Foundation (Brazil)', 'Max Vandervorst (Belgium)', 'Jerome Philip Kavanagh (New Zealand)',
                'Komako-Aroha Silva (New Zealand)', 'Refugee Musicians, UNHCR Rohingya camp (Bangladesh)',
            ])],
            ['left', 'Awards', $ul([
                '2003: Honorable Mention Award, Desh Natok, Dhaka',
                '2004: Honorable Mention Award, Nagorik Nattyangan, Dhaka',
                '2004: Media Best Award, Faculty of Fine Arts, Dhaka',
                '2007: Sacatar Foundation Fellowship, USA',
                '2009: “Tanusree Podok 2009” Best Young Theater Activist Award',
                '2014: NTOK-CPI Fellowship 2014, Seoul, South Korea',
                '2015: Best Musical Band, Citycell-Channel I Music Award',
            ])],
            ['right', 'Workshops Attended (Major)', $ul([
                '1996: Workshop on Puppet and Paper mask conducted by Amy Trumpeter (U.S.A.)',
                '1999: Workshop on ‘GRIPS’-Theater Berlin, the idea background and the acting techniques, conducted by Thomas Ahrens (Germany)',
                '2001: Musical workshop on ‘Brass Instrument’ organized by International Music School, Dhaka',
                '2001: Workshop on ‘Flute (classical)’ conducted by Bari Siddique, Alliance Francaise de Dacca',
                '2003: Playwright workshop organized by Bangladesh Group Theater Federation',
                '2006: Workshop on Video Art, organized by Britto Art Trust, Dhaka',
                '2008: World Perc Pan, Bahia, Brazil',
                '2008: Workshop on Digital Filmmaking, organized by British Council, Bangladesh',
                '2012: Soundcamp South Asia, Colombo, Sri Lanka',
                '2013: Worldtronics, Berlin, Germany',
                '2010-23: CRACK International Art Camp, Kustia, Bangladesh',
            ])],
            ['right', 'Performing Art', '<p><b>Actor (on stage)</b><br>1. Palakrome Barbar, 2. Adab, 3. Joyjoyonti, 4. Dui Boloder Golpo, 5. Kandte Mana, 6. Circus Circus, 7. Aronay Sangbad, 8. A Man for All Seasons, 9. Koinnya, 10. Bish Panchali, 11. Jontu, 12. Dorir Khela, 13. Jhora Phool, 14. Raja ebong Onnanyo</p>'
                .'<p><b>Actor (on screen)</b><br>Regularly acted in several Film, Tele Film, Tele Drama &amp; Serials</p>'
                .'<p><b>Performance Art</b><br>1. 2001 - Vomra (Love), 2. 2001 - Mirror Game (Power), 3. 2003 - Pink Man, 4. 2005 - Black &amp; White, 5. 2005 - Mon Monia (Soul), 6. 2008 - “Luz da vida”, 7. 2008 - Lullabai BA.BA, 8. 2010 - To Dear Father, 9. Bells tree, 10. Operation theater, 11. 2011 - Ondorjatra, 12. 2011 to Present - Laaljatra, 13. 2017 - 100% Pure, 14. Made in Bangladesh, 15. Blowing in the Wind</p>'
                .'<p><b>Open Air Street Improvisation</b><br>1. Boom, 2. Fouj Holt, 3. Ole ole Ola (Bahia), 4. Laaljatra</p>'
                .'<p><b>Playwright</b><br>1. Bish Panchali, 2. No Man’s Land, 3. Mandar, 4. Laal, 5. 2X2=4, 6. Ural, 7. Shataroop (Based on Tagore)</p>'
                .'<p><b>Direction</b><br>1. No Man’s Land, 2. Mandar, 3. Sinduk, 4. 2X2=4, 5. Ural</p>'
                .'<p><b>Mask and Props Making</b><br>1. Iblish, 2. Dhrubo Tara, 3. Circus Circus, 4. Joyjoyonti, 5. Lebedev, 6. Halum Halum, 7. Adab, 8. Bahe Nidhua Pathar</p>'
                .'<p><b>Video Art</b><br>1. Oh! Dream, 2. The Storyteller, 3. Shadow Opera, 4. Time &amp; Tide, 5. Inner ‘i’</p>'],
        ];
        // Same shape the editor saves: one <p> per line, an empty <p><br></p> as the gap between groups
        $editorShape = fn (string $html) => strtr($html, ['</p><p>' => '</p><p><br></p><p>', '<br>' => '</p><p>']);
        foreach ($blocks as $n => [$side, $title, $body]) {
            AboutBlock::create(['side' => $side, 'title' => $title, 'body' => $editorShape($body), 'sort' => $n + 1]);
        }
    }
}
