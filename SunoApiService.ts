// src/services/SunoApiService.ts
// Suno API ile entegrasyon için servis
class SunoApiService {
  private static instance: SunoApiService;
  private readonly API_URL = 'https://apibox.erweima.ai';
  private API_KEY: string | null = null;
  
  private constructor() {
    // API anahtarını bir yerden yükleme (örn. çevre değişkenleri)
    this.loadApiKey();
  }
  
  public static getInstance(): SunoApiService {
    if (!SunoApiService.instance) {
      SunoApiService.instance = new SunoApiService();
    }
    return SunoApiService.instance;
  }
  
  private loadApiKey(): void {
    // Gerçek uygulamada, bu güvenli bir şekilde yapılmalıdır
    // Örneğin bir backend üzerinden veya .env dosyasından
    const storedKey = localStorage.getItem('suno_api_key');
    if (storedKey) {
      this.API_KEY = storedKey;
    }
  }
  
  // API anahtarını ayarla
  public setApiKey(key: string): void {
    this.API_KEY = key;
    localStorage.setItem('suno_api_key', key);
  }
  
  // API anahtarını kontrol et
  public hasApiKey(): boolean {
    return this.API_KEY !== null && this.API_KEY.trim() !== '';
  }
  
  // API isteği gönderme temel fonksiyonu
  private async request<T>(endpoint: string, data: any): Promise<T> {
    if (!this.hasApiKey()) {
      throw new Error('API anahtarı ayarlanmamış');
    }
    
    try {
      const response = await fetch(`${this.API_URL}${endpoint}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${this.API_KEY}`
        },
        body: JSON.stringify(data)
      });
      
      const result = await response.json();
      
      if (result.code !== 200) {
        throw new Error(`API hatası: ${result.msg}`);
      }
      
      return result as T;
    } catch (error) {
      console.error('API isteği başarısız:', error);
      throw error;
    }
  }
  
  // Müzik Oluşturma API'si
  public async generateMusic(params: {
    prompt: string;
    customMode?: boolean;
    instrumental?: boolean;
    model?: 'V3_5' | 'V4';
    lyrics?: string;
    callBackUrl?: string;
  }): Promise<{
    taskId: string;
    estimatedTime: number;
  }> {
    return this.request<{
      code: number;
      msg: string;
      data: {
        taskId: string;
        estimatedTime: number;
      }
    }>('/api/v1/generate', params)
      .then(result => result.data);
  }
  
  // Şarkı Sözü Oluşturma API'si
  public async generateLyrics(params: {
    prompt: string;
    callBackUrl?: string;
  }): Promise<{
    taskId: string;
    estimatedTime: number;
  }> {
    return this.request<{
      code: number;
      msg: string;
      data: {
        taskId: string;
        estimatedTime: number;
      }
    }>('/api/v1/lyrics', params)
      .then(result => result.data);
  }
  
  // Görev Durumu Sorgulama API'si
  public async checkTaskStatus(taskId: string): Promise<{
    status: 'pending' | 'processing' | 'completed' | 'failed';
    progress?: number;
    result?: any;
  }> {
    return this.request<{
      code: number;
      msg: string;
      data: {
        status: 'pending' | 'processing' | 'completed' | 'failed';
        progress?: number;
        result?: any;
      }
    }>('/api/v1/status', { taskId })
      .then(result => result.data);
  }
  
  // Müzik WAV Formatına Dönüştürme API'si
  public async convertToWav(params: {
    audioUrl: string;
    callBackUrl?: string;
  }): Promise<{
    taskId: string;
    estimatedTime: number;
  }> {
    return this.request<{
      code: number;
      msg: string;
      data: {
        taskId: string;
        estimatedTime: number;
      }
    }>('/api/v1/wav', params)
      .then(result => result.data);
  }
  
  // Vokal Ayırma API'si
  public async separateVocals(params: {
    audioUrl: string;
    callBackUrl?: string;
  }): Promise<{
    taskId: string;
    estimatedTime: number;
  }> {
    return this.request<{
      code: number;
      msg: string;
      data: {
        taskId: string;
        estimatedTime: number;
      }
    }>('/api/v1/separate', params)
      .then(result => result.data);
  }
  
  // BPM ve Anahtar Analizi (sahte API, gerçek API varsa değiştirilebilir)
  public async analyzeAudio(audioUrl: string): Promise<{
    bpm: number;
    key: string;
    scale: 'major' | 'minor';
    genre: string[];
    audioLength: number;
  }> {
    // Gerçek bir API yerine simülasyon
    console.log(`Analyzing audio: ${audioUrl}`);
    
    // API çağrısı simülasyonu
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve({
          bpm: Math.floor(Math.random() * 60) + 80, // 80-140 arası rastgele BPM
          key: ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'][Math.floor(Math.random() * 12)],
          scale: Math.random() > 0.5 ? 'major' : 'minor',
          genre: ['Synthwave', 'Cyberpunk', 'Ambient', 'Lo-Fi'].sort(() => 0.5 - Math.random()).slice(0, 2),
          audioLength: Math.floor(Math.random() * 60) + 120 // 120-180 saniye arası
        });
      }, 1500);
    });
  }
}

export default SunoApiService.getInstance();