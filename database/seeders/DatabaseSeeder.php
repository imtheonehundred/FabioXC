<?php

namespace Database\Seeders;

use App\Domain\Bouquet\Models\Bouquet;
use App\Domain\Epg\Models\Epg;
use App\Domain\Line\Models\Line;
use App\Domain\Line\Models\Package;
use App\Domain\Server\Models\Server;
use App\Domain\Stream\Models\Stream;
use App\Domain\Stream\Models\StreamCategory;
use App\Domain\User\Models\MemberGroup;
use App\Domain\Vod\Models\Episode;
use App\Domain\Vod\Models\Movie;
use App\Domain\Vod\Models\Series;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminGroup = MemberGroup::create([
            'group_name' => 'Administrators',
            'permissions' => ['*'],
        ]);

        $resellerGroup = MemberGroup::create([
            'group_name' => 'Resellers',
            'permissions' => ['lines', 'users', 'streams.view'],
        ]);

        User::create([
            'username' => 'admin',
            'password' => Hash::make('admin'),
            'email' => 'admin@xcvm.local',
            'member_group_id' => $adminGroup->id,
        ]);

        User::create([
            'username' => 'reseller',
            'password' => Hash::make('reseller'),
            'email' => 'reseller@xcvm.local',
            'member_group_id' => $resellerGroup->id,
        ]);

        $mainServer = Server::create([
            'server_name' => 'Main Server',
            'server_ip' => '192.168.1.100',
            'domain_name' => 'main.xcvm.local',
            'http_port' => 80,
            'rtmp_port' => 1935,
            'status' => 1,
            'is_main' => 1,
            'cpu_usage' => 23.5,
            'mem_usage' => 48.2,
            'disk_usage' => 35.1,
            'uptime' => '45 days, 12:34:56',
            'total_clients' => 342,
        ]);

        $lbServer = Server::create([
            'server_name' => 'Load Balancer 1',
            'server_ip' => '192.168.1.101',
            'domain_name' => 'lb1.xcvm.local',
            'http_port' => 80,
            'rtmp_port' => 1935,
            'status' => 1,
            'cpu_usage' => 15.8,
            'mem_usage' => 32.6,
            'disk_usage' => 22.3,
            'uptime' => '30 days, 8:12:00',
            'total_clients' => 189,
        ]);

        Server::create([
            'server_name' => 'Load Balancer 2',
            'server_ip' => '192.168.1.102',
            'http_port' => 80,
            'rtmp_port' => 1935,
            'status' => 0,
            'cpu_usage' => 0,
            'mem_usage' => 0,
            'disk_usage' => 41.7,
            'total_clients' => 0,
        ]);

        $liveCats = [];
        foreach (['Sports', 'News', 'Entertainment', 'Kids', 'Music', 'Documentary'] as $i => $name) {
            $liveCats[] = StreamCategory::create([
                'category_name' => $name,
                'category_type' => 'live',
                'cat_order' => $i,
            ]);
        }

        $movieCats = [];
        foreach (['Action', 'Comedy', 'Drama', 'Horror', 'Sci-Fi', 'Thriller'] as $i => $name) {
            $movieCats[] = StreamCategory::create([
                'category_name' => $name,
                'category_type' => 'movie',
                'cat_order' => $i,
            ]);
        }

        $seriesCats = [];
        foreach (['Drama Series', 'Comedy Series', 'Crime', 'Fantasy', 'Anime'] as $i => $name) {
            $seriesCats[] = StreamCategory::create([
                'category_name' => $name,
                'category_type' => 'series',
                'cat_order' => $i,
            ]);
        }

        $streamNames = [
            'ESPN HD', 'CNN International', 'BBC World News', 'Sky Sports 1', 'Discovery Channel',
            'National Geographic', 'Cartoon Network', 'MTV Music', 'HBO Live', 'Fox News',
            'Al Jazeera English', 'Eurosport 1', 'NBC Sports', 'ABC News Live', 'TLC HD',
            'History Channel', 'Animal Planet', 'Comedy Central', 'Nickelodeon', 'BET',
            'CNBC', 'Bloomberg TV', 'RT News', 'DW English', 'France 24',
        ];

        foreach ($streamNames as $i => $name) {
            Stream::create([
                'stream_display_name' => $name,
                'stream_source' => 'http://source.example.com/live/' . ($i + 1),
                'type' => 'live',
                'status' => $i < 18 ? 1 : ($i < 22 ? 0 : 3),
                'category_id' => $liveCats[$i % count($liveCats)]->id,
                'server_id' => $i % 2 === 0 ? $mainServer->id : $lbServer->id,
                'current_viewers' => $i < 18 ? rand(5, 200) : 0,
                'admin_enabled' => 1,
                'added' => now()->subDays(rand(1, 90)),
                'bitrate' => rand(2000, 8000),
                'resolution' => ['720p', '1080p', '4K'][rand(0, 2)],
            ]);
        }

        foreach (['Jazz FM', 'Classic Rock Radio', 'Pop Hits 100'] as $i => $name) {
            Stream::create([
                'stream_display_name' => $name,
                'stream_source' => 'http://source.example.com/radio/' . ($i + 1),
                'type' => 'radio',
                'status' => 1,
                'category_id' => $liveCats[4]->id,
                'server_id' => $mainServer->id,
                'admin_enabled' => 1,
                'added' => now()->subDays(rand(1, 30)),
            ]);
        }

        $movieData = [
            ['The Last Stand', 'Action', 8.1], ['Fast Track', 'Action', 7.2],
            ['Laugh Out Loud', 'Comedy', 6.8], ['The Funny Side', 'Comedy', 7.5],
            ['Broken Dreams', 'Drama', 8.5], ['Silent Words', 'Drama', 7.9],
            ['Night Terror', 'Horror', 6.2], ['The Haunting', 'Horror', 7.1],
            ['Star Voyage', 'Sci-Fi', 8.3], ['Quantum Leap', 'Sci-Fi', 7.7],
            ['Dark Pursuit', 'Thriller', 7.8], ['The Witness', 'Thriller', 8.0],
            ['Iron Fist 2', 'Action', 6.5], ['Time Warp', 'Sci-Fi', 7.3],
            ['The Getaway', 'Thriller', 7.6],
        ];

        $catMap = [];
        foreach ($movieCats as $c) $catMap[$c->category_name] = $c->id;

        foreach ($movieData as $i => [$title, $genre, $rating]) {
            Movie::create([
                'stream_display_name' => $title,
                'stream_source' => 'http://source.example.com/movie/' . ($i + 1) . '.mp4',
                'genre' => $genre,
                'rating' => (string) $rating,
                'rating_5based' => (int) round($rating / 2),
                'category_id' => $catMap[$genre] ?? $movieCats[0]->id,
                'server_id' => $mainServer->id,
                'cover' => 'https://placehold.co/300x450?text=' . urlencode($title),
                'plot' => "A thrilling {$genre} movie that keeps you on the edge of your seat.",
                'director' => ['John Smith', 'Jane Doe', 'Mike Johnson', 'Sarah Lee'][rand(0, 3)],
                'cast' => 'Actor A, Actor B, Actor C',
                'release_date' => (2020 + rand(0, 5)) . '-' . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . '-01',
                'duration' => rand(90, 150) . ' min',
                'status' => 1,
                'admin_enabled' => 1,
                'added' => now()->subDays(rand(1, 60)),
                'container_extension' => 'mp4',
            ]);
        }

        $seriesData = [
            ['Breaking Point', 'Drama Series', 5, 4],
            ['The Office Remix', 'Comedy Series', 3, 6],
            ['Cold Case Files', 'Crime', 4, 3],
            ['Realm of Shadows', 'Fantasy', 2, 8],
            ['Attack on Mars', 'Anime', 3, 5],
            ['The Wire 2.0', 'Crime', 6, 2],
            ['Midnight Tales', 'Drama Series', 4, 3],
        ];

        $serCatMap = [];
        foreach ($seriesCats as $c) $serCatMap[$c->category_name] = $c->id;

        foreach ($seriesData as [$title, $genre, $seasons, $epsPerSeason]) {
            $series = Series::create([
                'title' => $title,
                'genre' => $genre,
                'category_id' => $serCatMap[$genre] ?? $seriesCats[0]->id,
                'cover' => 'https://placehold.co/300x450?text=' . urlencode($title),
                'plot' => "An acclaimed {$genre} that follows compelling characters through dramatic arcs.",
                'cast' => 'Star A, Star B, Star C',
                'rating' => (string) (rand(60, 95) / 10),
                'admin_enabled' => 1,
            ]);

            for ($s = 1; $s <= $seasons; $s++) {
                for ($e = 1; $e <= $epsPerSeason; $e++) {
                    Episode::create([
                        'series_id' => $series->id,
                        'season_number' => $s,
                        'episode_number' => $e,
                        'title' => "Episode {$e}",
                        'stream_source' => "http://source.example.com/series/{$series->id}/s{$s}e{$e}.mp4",
                        'container_extension' => 'mp4',
                        'status' => 1,
                        'admin_enabled' => 1,
                        'server_id' => $mainServer->id,
                        'added' => now()->subDays(rand(1, 90)),
                    ]);
                }
            }
        }

        Epg::create(['epg_name' => 'Main EPG', 'epg_url' => 'http://epg.example.com/xmltv.xml']);
        Epg::create(['epg_name' => 'Sports EPG', 'epg_url' => 'http://epg.example.com/sports.xml']);

        $bouquet1 = Bouquet::create([
            'bouquet_name' => 'Basic Package',
            'bouquet_channels' => json_encode([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            'bouquet_movies' => json_encode([1, 2, 3, 4, 5]),
            'bouquet_series' => json_encode([1, 2]),
        ]);

        $bouquet2 = Bouquet::create([
            'bouquet_name' => 'Premium Package',
            'bouquet_channels' => json_encode(range(1, 25)),
            'bouquet_movies' => json_encode(range(1, 15)),
            'bouquet_series' => json_encode(range(1, 7)),
        ]);

        $basicPkg = Package::create([
            'package_name' => 'Basic Monthly',
            'is_trial' => 0,
            'is_official' => 1,
        ]);

        $premiumPkg = Package::create([
            'package_name' => 'Premium Monthly',
            'is_trial' => 0,
            'is_official' => 1,
        ]);

        $trialPkg = Package::create([
            'package_name' => '24h Trial',
            'is_trial' => 1,
            'is_official' => 1,
        ]);

        $lineNames = [
            'john_doe', 'jane_smith', 'mike_wilson', 'sarah_jones', 'alex_brown',
            'chris_taylor', 'pat_anderson', 'sam_martinez', 'jordan_thomas', 'casey_white',
            'demo_user', 'test_line', 'vip_customer', 'reseller_line1', 'reseller_line2',
        ];

        foreach ($lineNames as $i => $name) {
            $line = Line::create([
                'username' => $name,
                'password' => 'pass' . ($i + 1),
                'exp_date' => $i < 12 ? now()->addDays(rand(10, 365)) : now()->subDays(rand(1, 30)),
                'max_connections' => $i < 10 ? rand(1, 3) : 1,
                'is_trial' => $i >= 10 && $i < 12 ? 1 : 0,
                'admin_enabled' => $i < 13 ? 1 : 0,
                'bouquet' => [$bouquet1->id, $bouquet2->id],
                'active_connections' => $i < 8 ? rand(0, 2) : 0,
                'created_by' => 1,
                'added' => now()->subDays(rand(1, 120)),
            ]);

            $line->packages()->attach($i < 5 ? $premiumPkg->id : $basicPkg->id);
        }

        DB::table('settings')->insert([
            ['key' => 'server_name', 'value' => 'XC_VM Panel', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'timezone', 'value' => 'UTC', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'default_language', 'value' => 'en', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'max_connections_per_line', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'trial_duration_hours', 'value' => '24', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'auto_restart_streams', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'epg_update_interval', 'value' => '24', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('activity_logs')->insert([
            ['user_id' => 1, 'action' => 'login', 'description' => 'Admin logged in', 'ip_address' => '192.168.1.1', 'created_at' => now()->subMinutes(5), 'updated_at' => now()->subMinutes(5)],
            ['user_id' => 1, 'action' => 'stream.create', 'description' => 'Created stream ESPN HD', 'ip_address' => '192.168.1.1', 'created_at' => now()->subMinutes(30), 'updated_at' => now()->subMinutes(30)],
            ['user_id' => 1, 'action' => 'line.create', 'description' => 'Created line john_doe', 'ip_address' => '192.168.1.1', 'created_at' => now()->subHour(), 'updated_at' => now()->subHour()],
            ['user_id' => 1, 'action' => 'server.restart', 'description' => 'Restarted Load Balancer 1', 'ip_address' => '192.168.1.1', 'created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2)],
            ['user_id' => 1, 'action' => 'settings.update', 'description' => 'Updated timezone to UTC', 'ip_address' => '192.168.1.1', 'created_at' => now()->subHours(3), 'updated_at' => now()->subHours(3)],
        ]);
    }
}
