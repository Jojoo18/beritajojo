function generateSlug(text) {
  return text
    .toString()
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9 -]/g, '') // Hapus karakter tidak valid
    .replace(/\s+/g, '-') // Ganti spasi dengan -
    .replace(/-+/g, '-') // Hilangkan - berturut-turut
    .replace(/^-+|-+$/g, ''); // Hilangkan - di awal/akhir
}
