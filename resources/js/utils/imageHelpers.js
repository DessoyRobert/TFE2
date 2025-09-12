export const FALLBACK_IMG =
  'https://res.cloudinary.com/djllwl8c0/image/upload/v1753292540/Logo-JarvisTech-PNG-normalsansfond_pgxlrj.png';

export function isCloudinary(url) {
  return !!url && url.includes('/upload/');
}

/**
 * Transforme une URL Cloudinary en ajoutant f_auto, q_auto, et largeur/hauteur si fournis.
 * @param {string|null|undefined} url
 * @param {number=} w largeur
 * @param {number=} h hauteur
 * @param {'fit'|'fill'=} fit
 * @returns {string}
 */
export function transformCloudinary(url, w, h, fit = 'fit') {
  if (!url) return FALLBACK_IMG;
  if (!isCloudinary(url)) return url;

  const mods = ['f_auto', 'q_auto'];
  if (w) mods.push(`w_${w}`);
  if (h) { mods.push(`h_${h}`, `c_${fit}`); }

  return url.replace('/upload/', `/upload/${mods.join(',')}/`);
}

/**
 * Retourne toujours une URL sûre (fallback si vide).
 * @param {string|null|undefined} url
 * @param {number=} w
 * @param {number=} h
 * @returns {string}
 */
export function safeImg(url, w = 160, h) {
  if (!url) return FALLBACK_IMG;
  return isCloudinary(url) ? transformCloudinary(url, w, h) : url;
}

export default { FALLBACK_IMG, isCloudinary, transformCloudinary, safeImg };