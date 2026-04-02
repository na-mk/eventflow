<template>
  <div class="max-w-3xl mx-auto space-y-8 text-slate-700 text-sm leading-relaxed">
    <div>
      <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Politique de Confidentialité</h2>
      <p class="text-slate-500 mt-1">Version 1.0 — En vigueur depuis le 30/03/2026</p>
    </div>

    <section>
      <h3 class="font-bold text-slate-900 mb-2">1. Responsable du traitement</h3>
      <p>EVENTFLOW — Projet intégrateur M2 Lead Dev Full Stack 2025-2026.<br />
      DPO fictif : dpo@eventflow.local</p>
    </section>

    <section>
      <h3 class="font-bold text-slate-900 mb-2">2. Données collectées</h3>
      <p>Nous collectons les données suivantes lors de votre inscription :</p>
      <ul class="list-disc ml-5 mt-2 space-y-1">
        <li>Nom et prénom</li>
        <li>Adresse email</li>
        <li>Numéro de téléphone (optionnel)</li>
        <li>Date et version du consentement</li>
        <li>Adresse IP lors des connexions (stockée sous forme de hash SHA-256)</li>
      </ul>
    </section>

    <section>
      <h3 class="font-bold text-slate-900 mb-2">3. Base légale</h3>
      <p>Le traitement de vos données repose sur votre <strong>consentement explicite</strong> (Art. 6.1.a RGPD) recueilli lors de votre inscription.</p>
    </section>

    <section>
      <h3 class="font-bold text-slate-900 mb-2">4. Finalités du traitement</h3>
      <ul class="list-disc ml-5 space-y-1">
        <li>Gestion de votre compte et authentification</li>
        <li>Inscription aux événements professionnels</li>
        <li>Communication relative aux événements</li>
        <li>Conformité RGPD et journalisation des accès</li>
      </ul>
    </section>

    <section>
      <h3 class="font-bold text-slate-900 mb-2">5. Durée de conservation</h3>
      <ul class="list-disc ml-5 space-y-1">
        <li>Données de compte : 3 ans après la dernière activité</li>
        <li>Logs de consentement : 5 ans (obligation légale)</li>
        <li>Données anonymisées : conservation illimitée</li>
      </ul>
    </section>

    <section>
      <h3 class="font-bold text-slate-900 mb-2">6. Vos droits (RGPD)</h3>
      <ul class="list-disc ml-5 space-y-1">
        <li><strong>Art. 15</strong> — Droit d'accès à vos données</li>
        <li><strong>Art. 16</strong> — Droit de rectification</li>
        <li><strong>Art. 17</strong> — Droit à l'effacement (anonymisation)</li>
        <li><strong>Art. 20</strong> — Droit à la portabilité (export JSON)</li>
        <li><strong>Art. 21</strong> — Droit d'opposition</li>
      </ul>
      <p class="mt-3">
        Exercez vos droits depuis la page
        <router-link to="/profile" class="text-indigo-600 hover:underline font-medium">Mes données</router-link>
        ou en contactant : dpo@eventflow.local
      </p>
    </section>

    <section>
      <h3 class="font-bold text-slate-900 mb-2">7. Sécurité</h3>
      <p>Les mots de passe sont hashés avec bcrypt. Les tokens JWT sont signés et ont une durée de validité de 1 heure. Les adresses IP sont stockées sous forme de hash SHA-256 irréversible.</p>
    </section>

    <section>
      <h3 class="font-bold text-slate-900 mb-2">8. Contact</h3>
      <p>Pour toute question relative à vos données personnelles : <a href="mailto:dpo@eventflow.local" class="text-indigo-600 hover:underline">dpo@eventflow.local</a></p>
    </section>
  </div>
</template>
