# Skilaverkefni 2 - Music Search Module

### Leiðbeiningar: 

Velkomin í tónlistarskáningarkerfið kæri notandi.

Í þessu kerfi getur þú búið til listamann, plötu og lag með gögnum frá spotify og discogs og skráð það inní kerfið.

Fyrsta skref er að fara í extend flipann þar sem allar módúlur eru staðsettar og installa módúlunni okkar.
‘Music Search’ er aðal módúlan okkar, þú finnur hana undir ‘Ari & Elmar INC’. En til að fá hana til að virka þurfum við að installa ‘Music Search’ módulinni, það kemur síðan upp gluggi sem spyr þig hvort það sé í lagi að installa líka "Spotify_Lookup" og "Discogs_Lookup" (Dependancy Injection). Þegar það er búið þá smellir þú á configure hnappinn á "Spotify_Lookup" og "Discogs_Lookup" módulunum, þar getur þú sett þú inn API lykla. Ýttu svo á ’Save configuration’ fyrir báðar módulunar. Fræmkvæmdu þetta skref fyrir bæði Spotify og Discogs módúlurnar. 

#### Configure fyrir Spotify og Discogs lýtur svona út

Spotify Lookup:
- Spotify API Client ID
- Spotify API Client Secret

Discogs Lookup:
- Discogs API token 

Núna getum við byrjað að nota ‘Music Search’ módúlúna.

Næst ferðu í content flipann og finnur Music Search.  Þar færðu upp valmöguleika: Create Album, Create Artist og Create Track.

### Create Artist:
1. Hér getur þú leitað af listamanni með því að slá inn nafn að eigin vali. Virknin framkvæmir leitina af listamanni í gegnum API köll bæði frá spotify og discogs. Ýttu svo á ‘næsta skref’.
2. Hér fáum við niðurstöður eftir leit frá spotify og discogs. Hér velur þú báðar upplýsingar um listamann frá spotify og discogs. Ýttu svo á ‘next’.
3. Hér fáum við aftur niðurstöður frá báðum þjónustum um titil, mynd, lýsingu, tegund og vefsíðu þar sem þú getur notað að eigin vali. Lýsingin kemur sjálfkrafa inn frá discogs. Ýttu svo á ‘Create Artist.’
4. Listamaður er búinn til og skráður í kerfið.


### Create Album:
1. Hér getur þú leitað af albúmi eftir listamanni með því að slá inn efst nafn listamanns og nafn albúms fyrir neðan. Athugið að listamaður þarf að vera til í kerfinu. Ýttu svo á ‘next’.
2. Hér fáum við niðurstöður eftir leit frá spotify og discogs. Hér velur þú báðar upplýsingar um albúmið frá spotify og discogs. Ýttu svo á ‘next’.
3. Hér fáum við aftur niðurstöður frá báðum þjónustum um titil, mynd, lýsingu, tegund, útgáfudag, meðlimi þar sem þú getur notað að eigin vali. Útgáfudagurinn kemur sjálfkrafa inn. Hér getur þú líka leitað eftir útgáfufyrirtæki og einnig valið hvort albúmið sé highlighted á forsíðunni. Ýttu svo á ‘Create Album.’
4. Albúm er búið til og skráð í kerfið.

### Create Track:
1. Hér getur þú leitað af lagi eftir listamanni með því að slá inn efst nafn listamanns og nafn lags fyrir neðan. Athugið að listamaður þarf að vera til í kerfinu. Ýttu svo á ‘next’.
2. Hér fáum við niðurstöður eftir leit frá Spotify og Discogs. Hér velur þú báðar upplýsingar um lagið frá báðum þjónustum. Ýttu svo á ‘next’.
3. Hér fáum við upplýsingar um lög frá þjónustum sem þú getur notað að eigin vali. Einnig getur þú valið tegund fyrir lagið. Ýttu svo á ‘Create Track’.
4. Lag er búið til og er skráð í kerfið.
