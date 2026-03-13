# प्रोजेक्ट पूरा भएपछि व्यवस्थापन (Completed Project Management)

प्रोजेक्ट complete भएपछि यसरी manage गर्न सकिन्छ।

---

## १. स्टेटस अपडेट गर्ने

- Project Edit मा जानुहोस् → **Status** मा **"Completed"** छान्नुहोस् → Save।
- यसले project लाई completed को रूपमा चिन्न मद्दत गर्छ र list मा नीलो (blue) badge देखिन्छ।

**सुझाव:** जब Work Progress 100% भयो वा सबै काम सकियो भने एकपटक Status **Completed** मा राख्नुहोस्।

---

## २. End Date राख्ने

- Project Edit मा **End Date** मा वास्तविक समाप्ति मिति राख्नुहोस्।
- Report र timeline हेर्दा कति समय लाग्यो भन्ने थाहा हुन्छ।

---

## ३. Completed प्रोजेक्टहरू अलग देख्ने (Filter)

Projects पृष्ठमा:

- **सबै** – planned, active, on_hold, completed, cancelled सबै।
- **Active मात्र** – चलिरहेका (active, on_hold)।
- **Completed मात्र** – सकिएका प्रोजेक्टहरू।

यो filter ट्याब वा dropdown बनाउन सकिन्छ ताकि completed projects अलग section मा वा filter लगाएर हेर्न सकियोस्।

---

## ४. पूरा भएको प्रोजेक्टको जानकारी राख्ने

Complete गर्दा यी चीजहरू एकपटक check/record गर्नु राम्रो:

| कुरा | कहाँ देख्ने/राख्ने |
|------|---------------------|
| अन्तिम खर्च (total expense) | Expenses / Reports |
| Completed work (quantity र amount) | Project को Work Progress bar र Completed Work |
| Material usage | Completed Work → Material Usages |
| BOQ vs actual | BOQ Work Items vs Completed Work Record Items |
| Gallery / photos | Project → Gallery |

यी सबै पहिले नै app मा छन्; completed project को लागि एउटा **Project Detail / View** बाट सबै summary हेर्न मिल्छ।

---

## ५. Closure Note (वैकल्पिक)

कुनै project complete गर्दा छोटो note राख्न चाहनुहुन्छ भने:

- Project table मा `completion_notes` वा `closure_notes` (text) column थप्न सकिन्छ।
- वा Description मा नै अन्तिम लाइनमा "Completed on YYYY-MM-DD. Notes: ..." लेख्न सकिन्छ।

---

## ६. Recommended workflow (क्रम)

1. सबै काम र measurement book / completed work दर्ता गर्नु।
2. Project को **Status** → **Completed** गर्नु।
3. **End Date** set गर्नु।
4. (Optional) Description वा closure note मा completion note लेख्नु।
5. Gallery मा अन्तिम photos राख्नु (यदि चाहिन्छ)।
6. पछि report चाहिएमा Projects list बाट **Completed** filter लगाएर हेर्नु वा View बाट summary हेर्नु।

---

## ७. भविष्यमा थप्न सकिने (Optional)

- **Completed projects** को लागि एउटा अलग page/section: "Completed Projects"।
- Export: completed project को summary (work done, materials, dates) PDF/Excel।
- **completed_at** date column: status "completed" गर्दा automatically आजको date save गर्ने।

---

तपाईंको app मा status **completed** र project–wise progress पहिले नै छ। ऊपरको workflow अनुसार status र end date मात्र सही राख्नुहोस्; बाँकी report र detail View बाट नै manage गर्न सकिन्छ।
