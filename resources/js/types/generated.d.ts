declare namespace App {
namespace Data {
export type PivotData = {
id: number,
};
export type PlaylistData = {
id: number,
name: string,
is_primary: boolean,
playlist_songs_count: number | null,
};
export type SongData = {
id: number,
title: string,
artist: string,
album: string | null,
genre: string | null,
duration_seconds: number | null,
mime_type: string,
uploader: App.Data.UploaderData,
is_in_my_library: boolean,
is_uploader: boolean,
audio_url: string,
my_playlist_ids: number[],
pivot: App.Data.PivotData | null,
created_at: string | null,
};
export type UploaderData = {
id: number,
name: string | null,
};
}
}
