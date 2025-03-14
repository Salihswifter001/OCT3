// src/services/DragDropHelper.ts

/**
 * Bir elemente sürüklenebilir özellik ekler
 * @param element - Sürüklenebilir yapılacak HTML elementi
 * @param data - Sürüklenen veri
 * @param onDragStart - Sürükleme başladığında çalışacak fonksiyon
 * @param onDragEnd - Sürükleme bittiğinde çalışacak fonksiyon
 */
 export function makeDraggable(
  element: HTMLElement,
  data: any,
  onDragStart?: (e: DragEvent, data: any) => void,
  onDragEnd?: (e: DragEvent) => void
) {
  element.setAttribute('draggable', 'true');
  
  element.addEventListener('dragstart', (e) => {
    // Element için dragging class ekle
    element.classList.add('is-dragging');
    
    // Veriyi ayarla
    if (e.dataTransfer) {
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('application/json', JSON.stringify(data));
    }
    
    // Callback çağır
    if (onDragStart) {
      onDragStart(e as DragEvent, data);
    }
  });
  
  element.addEventListener('dragend', (e) => {
    // Element için dragging class kaldır
    element.classList.remove('is-dragging');
    
    // Callback çağır
    if (onDragEnd) {
      onDragEnd(e as DragEvent);
    }
  });
}

/**
 * Bir elementi drop target yapar
 * @param element - Hedef HTML element
 * @param onDragOver - Sürüklenen eleman üzerine geldiğinde çalışacak fonksiyon
 * @param onDrop - Bırakıldığında çalışacak fonksiyon
 */
export function makeDropTarget(
  element: HTMLElement,
  onDragOver?: (e: DragEvent) => void,
  onDrop?: (e: DragEvent, data: any) => void
) {
  element.addEventListener('dragover', (e) => {
    // Default işlemi engelle
    e.preventDefault();
    
    // Callback çağır
    if (onDragOver) {
      onDragOver(e as DragEvent);
    }
    
    // Drop efekti ekle
    if (!(e.target as HTMLElement).classList.contains('drag-over')) {
      (e.target as HTMLElement).classList.add('drag-over');
    }
    
    if (e.dataTransfer) {
      e.dataTransfer.dropEffect = 'move';
    }
  });
  
  element.addEventListener('dragleave', (e) => {
    // Drop efektini kaldır
    (e.target as HTMLElement).classList.remove('drag-over');
  });
  
  element.addEventListener('drop', (e) => {
    e.preventDefault();
    
    // Drop efektini kaldır
    (e.target as HTMLElement).classList.remove('drag-over');
    
    // Veriyi al
    let data;
    try {
      const jsonData = e.dataTransfer?.getData('application/json');
      if (jsonData) {
        data = JSON.parse(jsonData);
      }
    } catch (error) {
      console.error('Drop verisi ayrıştırılamadı', error);
      return;
    }
    
    // Callback çağır
    if (onDrop && data) {
      onDrop(e as DragEvent, data);
    }
  });
}

/**
 * Bırakma göstergesi ekle
 * @param containerElement - Göstergenin ekleneceği konteyner
 * @param position - Göstergenin pozisyonu
 * @returns - Gösterge elementi
 */
export function addDropIndicator(
  containerElement: HTMLElement,
  position: { x: number, y: number }
): HTMLElement {
  const indicator = document.createElement('div');
  indicator.className = 'drop-indicator';
  indicator.style.left = `${position.x}px`;
  indicator.style.top = `${position.y}px`;
  
  containerElement.appendChild(indicator);
  
  return indicator;
}

/**
 * Bırakma göstergesini kaldır
 * @param indicator - Kaldırılacak gösterge elementi
 */
export function removeDropIndicator(indicator: HTMLElement): void {
  if (indicator.parentElement) {
    indicator.parentElement.removeChild(indicator);
  }
}

/**
 * İki elementin yer değiştirme animasyonu
 * @param element1 - Birinci element
 * @param element2 - İkinci element
 * @param duration - Animasyon süresi (ms)
 * @param onComplete - Animasyon tamamlandığında çalışacak fonksiyon
 */
export function swapElements(
  element1: HTMLElement,
  element2: HTMLElement,
  duration: number = 300,
  onComplete?: () => void
): void {
  // Element pozisyonlarını al
  const rect1 = element1.getBoundingClientRect();
  const rect2 = element2.getBoundingClientRect();
  
  // Hareket mesafelerini hesapla
  const x1 = rect2.left - rect1.left;
  const y1 = rect2.top - rect1.top;
  const x2 = rect1.left - rect2.left;
  const y2 = rect1.top - rect2.top;
  
  // Animasyon başlama zamanı
  const startTime = performance.now();
  
  // Animasyon fonksiyonu
  const animate = (time: number) => {
    const elapsed = time - startTime;
    const progress = Math.min(elapsed / duration, 1);
    
    // Easing fonksiyonu (ease-out)
    const easeOut = (t: number) => 1 - Math.pow(1 - t, 2);
    const easedProgress = easeOut(progress);
    
    // Elementleri hareket ettir
    element1.style.transform = `translate(${x1 * easedProgress}px, ${y1 * easedProgress}px)`;
    element2.style.transform = `translate(${x2 * easedProgress}px, ${y2 * easedProgress}px)`;
    
    // Animasyon tamamlandı mı?
    if (progress < 1) {
      requestAnimationFrame(animate);
    } else {
      // Animasyon tamamlandı, temizlik yap
      element1.style.transform = '';
      element2.style.transform = '';
      
      // DOM'da gerçekten yer değiştir
      const parent1 = element1.parentNode;
      const sibling1 = element1.nextSibling === element2 ? element1 : element1.nextSibling;
      
      // Element2'yi element1'in yerine koy
      parent1?.replaceChild(element2, element1);
      
      // Element1'i element2'nin yerine koy
      if (sibling1) {
        sibling1.parentNode?.insertBefore(element1, sibling1);
      } else {
        parent1?.appendChild(element1);
      }
      
      // Tamamlandı callback'ini çağır
      if (onComplete) {
        onComplete();
      }
    }
  };
  
  // Animasyonu başlat
  requestAnimationFrame(animate);
}

/**
 * Elementlerin order özelliğini kullanarak sıralamayı güncelle
 * @param elements - Sıralanacak elementler
 * @param newOrder - Yeni sıralama (element key veya id'leri)
 */
export function reorderElements(
  elements: HTMLElement[],
  newOrder: string[]
): void {
  // Her element için yeni order değerini ayarla
  elements.forEach(element => {
    const key = element.getAttribute('data-key') || element.id;
    const newIndex = newOrder.indexOf(key);
    
    if (newIndex !== -1) {
      element.style.order = newIndex.toString();
    }
  });
}

/**
 * Element üzerindeki sürükle-bırak özelliklerini kaldır
 * @param element - Temizlenecek element
 */
export function cleanupDragDrop(element: HTMLElement): void {
  element.removeAttribute('draggable');
  element.classList.remove('is-dragging', 'drag-over');
  
  // Yeni bir kopya oluşturarak tüm event listener'ları kaldır
  const clone = element.cloneNode(true) as HTMLElement;
  if (element.parentNode) {
    element.parentNode.replaceChild(clone, element);
  }
}